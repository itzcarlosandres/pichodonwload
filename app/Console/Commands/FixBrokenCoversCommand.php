<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\Console as GameConsole;
use App\Services\RomScraperService;
use App\Services\ImageOptimizationService;
use App\Services\ScraperSafetyService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FixBrokenCoversCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roms:fix-broken-covers 
                            {--game= : ID específico de juego a reparar} 
                            {--console= : Filtrar por slug o nombre de consola (ej. nintendo-64)} 
                            {--all-cdromance : Reparar todos los juegos cuyo origen sea CDRomance} 
                            {--force : Forzar actualización en todos los juegos filtrados} 
                            {--dry-run : Simular la reparación sin modificar archivos ni base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repara y descarga carátulas HD auténticas para juegos con portadas rotas, logos de CDRomance o placeholders';

    /**
     * Execute the console command.
     */
    public function handle(
        RomScraperService $scraperService,
        ImageOptimizationService $imageService,
        ScraperSafetyService $safetyService
    ): int {
        $gameId = $this->option('game');
        $consoleFilter = $this->option('console');
        $allCdromance = (bool) $this->option('all-cdromance');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("🔍 Identificando juegos que requieren corrección de carátula...");

        $query = Game::with('console');

        if ($gameId) {
            $query->where('id', $gameId);
        } else {
            if ($consoleFilter) {
                $console = GameConsole::where('slug', $consoleFilter)
                    ->orWhere('name', 'LIKE', "%{$consoleFilter}%")
                    ->first();
                if ($console) {
                    $query->where('console_id', $console->id);
                }
            }

            if ($allCdromance) {
                $query->where(function ($q) {
                    $q->where('download_url', 'LIKE', '%cdromance.org%')
                      ->orWhere('download_links', 'LIKE', '%cdromance%')
                      ->orWhere('cover_url', 'LIKE', '%cdromance%')
                      ->orWhere('cover_url', 'LIKE', '%cdr-logo%');
                });
            } elseif (!$force) {
                // Filtro estándar de carátulas sospechosas o con logos
                $query->where(function ($q) {
                    $q->where('cover_url', 'LIKE', '%cdr-logo%')
                      ->orWhere('cover_url', 'LIKE', '%phoenix%')
                      ->orWhere('cover_url', 'LIKE', '%logo%')
                      ->orWhere('cover_url', 'LIKE', '%unsplash%')
                      ->orWhere('cover_url', 'LIKE', '%placeholder%')
                      ->orWhere('cover_url', 'LIKE', '%thumb-unavailable%')
                      ->orWhere('cover_url', 'LIKE', '%-psp-thumb-%')
                      ->orWhereNull('cover_url')
                      ->orWhere('cover_url', '');
                });
            }
        }

        $games = $query->orderBy('id', 'asc')->get();

        if ($games->isEmpty()) {
            $this->info("✨ ¡Excelente! No se encontraron juegos con portadas defectuosas o logos.");
            return self::SUCCESS;
        }

        $this->info("🎯 Se encontraron {$games->count()} juegos para procesar." . ($dryRun ? " [MODO SIMULACIÓN]" : ""));
        $this->newLine();

        $fixedCount = 0;
        $failedCount = 0;

        foreach ($games as $index => $game) {
            $num = $index + 1;
            $consoleName = $game->console ? $game->console->name : 'Retro';
            $consoleSlug = $game->console ? $game->console->slug : 'nintendo-64';

            $this->line("<fg=cyan>[{$num}/{$games->count()}]</> 🎮 <comment>#{$game->id} {$game->title}</comment> ({$consoleName})");
            $this->line("   Portada actual: <fg=gray>" . ($game->cover_url ?: 'VACÍA') . "</>");

            $targetCoverUrl = null;

            // 1. Si el juego tiene enlace a CDRomance, buscar ficha para extraer box art real
            $isCdromance = str_contains($game->download_url ?? '', 'cdromance') || 
                           str_contains($game->cover_url ?? '', 'cdromance') ||
                           str_contains($game->download_links ?? '', 'cdromance');

            if ($isCdromance) {
                $targetCoverUrl = $this->resolveCdromanceBoxArt($game, $scraperService, $safetyService);
            }

            // 2. Si no se obtuvo de CDRomance o no era de allí, buscar carátula oficial en Romspedia
            if (empty($targetCoverUrl) || $scraperService->isSiteLogoOrInvalid($targetCoverUrl)) {
                $this->line("   🔎 Buscando carátula 3D oficial en Romspedia...");
                $targetCoverUrl = $scraperService->searchRomspediaCover($game->title, $consoleSlug);
            }

            // 3. Si sigue sin portada, verificar si el archivo de Romspedia tiene URL directa
            if (empty($targetCoverUrl)) {
                $slug = Str::slug($game->title);
                $directUrl = "https://static.romspedia.com/webp/roms/{$slug}-cover.webp";
                $headers = @get_headers($directUrl);
                if ($headers && str_contains($headers[0], '200')) {
                    $targetCoverUrl = $directUrl;
                }
            }

            if (empty($targetCoverUrl) || $scraperService->isSiteLogoOrInvalid($targetCoverUrl)) {
                $this->warn("   ⚠️ No se encontró carátula externa disponible para '{$game->title}'.");
                $failedCount++;
                continue;
            }

            $this->line("   📸 Carátula encontrada: <fg=green>{$targetCoverUrl}</>");

            if ($dryRun) {
                $this->comment("   [DRY-RUN] Simulado: se habría descargado y convertido a WebP.");
                $fixedCount++;
                continue;
            }

            // 4. Descargar y procesar a WebP local
            try {
                $tempPath = tempnam(sys_get_temp_dir(), 'fixed_cover_');
                $ch = curl_init($targetCoverUrl);
                $fp = fopen($tempPath, 'wb');
                $host = parse_url($targetCoverUrl, PHP_URL_HOST) ?? 'www.romspedia.com';
                $referer = (parse_url($targetCoverUrl, PHP_URL_SCHEME) ?? 'https') . '://' . $host . '/';

                curl_setopt_array($ch, [
                    CURLOPT_FILE => $fp,
                    CURLOPT_HEADER => false,
                    CURLOPT_TIMEOUT => 25,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_REFERER => $referer,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                ]);

                curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                fclose($fp);

                if ($httpCode === 200 && file_exists($tempPath) && filesize($tempPath) > 500 && @getimagesize($tempPath) !== false) {
                    $uploadedFile = new UploadedFile($tempPath, 'cover.webp', 'image/webp', null, true);
                    $processed = $imageService->processCover($uploadedFile);

                    $game->update([
                        'cover_url' => $processed['url'],
                        'cover_thumb_url' => $processed['thumb_url'],
                    ]);

                    $this->info("   ✅ Portada HD actualizada y guardada localmente en WebP.");
                    $fixedCount++;
                } else {
                    // Si falla el procesamiento GD, guardar URL remota directa
                    $game->update([
                        'cover_url' => $targetCoverUrl,
                        'cover_thumb_url' => $targetCoverUrl,
                    ]);
                    $this->info("   ✅ Portada remota asignada directamente.");
                    $fixedCount++;
                }

                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }

            } catch (\Throwable $e) {
                $this->error("   ❌ Error procesando imagen: " . $e->getMessage());
                $failedCount++;
            }

            // Pequeña pausa para no sobrecargar los servidores de origen
            usleep(500000); // 0.5s
        }

        $this->newLine();
        $this->info("══════════════════════════════════════════════");
        $this->info("🎉 Reparación finalizada con éxito.");
        $this->info("   ✅ Corregidos: {$fixedCount}");
        if ($failedCount > 0) {
            $this->warn("   ⚠️ Sin corregir: {$failedCount}");
        }
        $this->info("══════════════════════════════════════════════");

        return self::SUCCESS;
    }

    /**
     * Resuelve el box art real de una entrada de CDRomance
     */
    protected function resolveCdromanceBoxArt(Game $game, RomScraperService $scraper, ScraperSafetyService $safety): ?string
    {
        $url = $game->download_url;

        // Si es URL directa de juego (no AJAX download.php)
        if ($url && str_contains($url, 'cdromance.org') && !str_contains($url, 'download.php')) {
            $scrape = $scraper->scrape($url);
            if (!empty($scrape['cover_url']) && !$scraper->isSiteLogoOrInvalid($scrape['cover_url'])) {
                return $scrape['cover_url'];
            }
        }

        // Si es enlace download.php o genérico, buscar la página del juego en CDRomance por título
        $searchUrl = 'https://cdromance.org/?s=' . urlencode($game->title);
        $searchRes = $safety->safeFetch($searchUrl, 'cdromance', [], true, 3600);

        if ($searchRes['success'] && !empty($searchRes['html'])) {
            if (preg_match('/<a class="cover-link" href="([^"]+)"/i', $searchRes['html'], $lm)) {
                $gamePageUrl = $lm[1];
                $scrape = $scraper->scrape($gamePageUrl);
                if (!empty($scrape['cover_url']) && !$scraper->isSiteLogoOrInvalid($scrape['cover_url'])) {
                    return $scrape['cover_url'];
                }
            }
        }

        return null;
    }
}
