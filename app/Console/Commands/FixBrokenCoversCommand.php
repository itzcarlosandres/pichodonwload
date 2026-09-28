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
                            {--all : Escanear y reparar todos los juegos sin excepción} 
                            {--force : Forzar actualización en todos los juegos seleccionados} 
                            {--dry-run : Simular la reparación sin modificar archivos ni base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta y repara carátulas defectuosas, logos de CDRomance convertidos a WebP o placeholders por portadas HD auténticas';

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
        $all = (bool) $this->option('all');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("🔍 Escaneando base de datos y archivos de carátula...");

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
            }
        }

        $candidates = $query->orderBy('id', 'asc')->get();

        if ($candidates->isEmpty()) {
            $this->info("✨ No se encontraron juegos según los filtros especificados.");
            return self::SUCCESS;
        }

        // Si se especificó --force o --all, procesar todos los candidatos
        // De lo contrario, usar detección inteligente (URLs con logo o archivos locales con peso/hash de logo)
        if ($force || $all) {
            $games = $candidates;
        } else {
            $games = $candidates->filter(function (Game $game) {
                return $this->isBrokenCover($game);
            })->values();
        }

        if ($games->isEmpty()) {
            $this->info("✨ ¡Excelente! Todas las carátulas inspeccionadas son válidas (no se detectaron logos ni portadas rotas).");
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

            // 1. Si el juego proviene de CDRomance, buscar ficha para extraer box art real
            $downloadLinksText = is_array($game->download_links) 
                ? json_encode($game->download_links) 
                : (string) ($game->download_links ?? '');

            $isCdromance = str_contains($game->download_url ?? '', 'cdromance') || 
                           str_contains($game->cover_url ?? '', 'cdromance') ||
                           str_contains($downloadLinksText, 'cdromance');

            if ($isCdromance) {
                $targetCoverUrl = $this->resolveCdromanceBoxArt($game, $scraperService, $safetyService);
            }

            // 2. Si no se obtuvo de CDRomance o no era de allí, buscar carátula oficial en Romspedia
            if (empty($targetCoverUrl) || $scraperService->isSiteLogoOrInvalid($targetCoverUrl)) {
                $this->line("   🔎 Buscando carátula 3D oficial en Romspedia...");
                $targetCoverUrl = $scraperService->searchRomspediaCover($game->title, $consoleSlug);
            }

            // 3. Si sigue sin portada, verificar si el archivo de Romspedia tiene URL directa basada en slug
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

                    // Limpiar archivo antiguo defectuoso del disco si existía localmente
                    $this->cleanupLocalCoverFile($game->cover_url);
                    $this->cleanupLocalCoverFile($game->cover_thumb_url);

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

            // Pausa de cortesía para no saturar servidores
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
     * Determina si la carátula de un juego está rota, vacía o contiene el logo de CDRomance
     */
    protected function isBrokenCover(Game $game): bool
    {
        $cover = $game->cover_url;

        // 1. Vacía o nula
        if (empty($cover)) {
            return true;
        }

        // 2. Coincidencias de texto obvias en la URL
        if (preg_match('/(?:cdr-logo|logo|phoenix|banner|header|unsplash|placeholder|thumb-unavailable|-psp-thumb-)/i', $cover)) {
            return true;
        }

        // 3. Inspección del archivo local en disco
        // Si la imagen fue descargada y convertida a WebP (ej: /uploads/covers/xxx.webp)
        $parsedPath = parse_url($cover, PHP_URL_PATH);
        if ($parsedPath) {
            $relativePath = ltrim($parsedPath, '/');
            $localPath = public_path($relativePath);

            if (!file_exists($localPath)) {
                $storagePath = storage_path('app/public/' . preg_replace('#^storage/#', '', $relativePath));
                if (file_exists($storagePath)) {
                    $localPath = $storagePath;
                }
            }

            if (file_exists($localPath)) {
                $fileSize = filesize($localPath);
                // El logo de CDRomance convertido a WebP 600x820 pesa entre 6.8KB y 7.4KB.
                // Una carátula auténtica HD a 600x820 pesa entre 35KB y 200KB.
                // Todo archivo menor a 15KB o con hash del logo es defectuoso.
                if ($fileSize < 15000) {
                    return true;
                }

                $md5 = md5_file($localPath);
                if (in_array($md5, [
                    'c2028ac818ff3793ffc52a3f2bcc104a', // CDRomance phoenix logo
                    '508d3b1348550f475f09dde36da4e830', // CDRomance 900x272 banner logo
                ])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resuelve el box art real de una entrada de CDRomance
     */
    protected function resolveCdromanceBoxArt(Game $game, RomScraperService $scraper, ScraperSafetyService $safety): ?string
    {
        $url = $game->download_url;

        // Si no está en download_url, comprobar si está guardado en download_links
        if ((!$url || !str_contains($url, 'cdromance.org')) && is_array($game->download_links)) {
            foreach ($game->download_links as $l) {
                if (!empty($l['url']) && str_contains($l['url'], 'cdromance.org')) {
                    $url = $l['url'];
                    break;
                }
            }
        }

        // 1. Si es URL directa de juego (no AJAX download.php)
        if ($url && str_contains($url, 'cdromance.org') && !str_contains($url, 'download.php')) {
            $scrape = $scraper->scrape($url);
            if (!empty($scrape['cover_url']) && !$scraper->isSiteLogoOrInvalid($scrape['cover_url'])) {
                return $scrape['cover_url'];
            }
        }

        // 2. Buscar en CDRomance por título limpio (sin paréntesis como [USA] o [Europe])
        $cleanTitle = trim(preg_replace('/\s*(?:\([^)]*\)|\[[^\]]*\])/', '', $game->title));
        $searchUrl = 'https://cdromance.org/?s=' . urlencode($cleanTitle);
        $searchRes = $safety->safeFetch($searchUrl, 'cdromance', [], true, 3600);

        if ($searchRes['success'] && !empty($searchRes['html'])) {
            if (preg_match_all('/<div class="game-container">(.*?)<\/div>\s*<\/div>/is', $searchRes['html'], $blocks)) {
                $consoleSlug = $game->console ? strtolower($game->console->slug) : '';
                $bestUrl = null;
                $bestScore = 0;

                foreach ($blocks[0] as $block) {
                    if (!preg_match('/<a class="cover-link" href="([^"]+)"/i', $block, $lm)) {
                        continue;
                    }
                    $candidateUrl = $lm[1];
                    $candidateTitle = '';
                    if (preg_match('/<div class="game-title">([^<]+)<\/div>/i', $block, $tm)) {
                        $candidateTitle = trim(html_entity_decode(strip_tags($tm[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    }
                    $candidateConsole = '';
                    if (preg_match('/<div class="console[^"]*"[^>]*>([^<]+)<\/div>/i', $block, $cm)) {
                        $candidateConsole = strtolower(trim($cm[1]));
                    }

                    similar_text(strtolower($cleanTitle), strtolower($candidateTitle), $score);
                    if ($consoleSlug && $candidateConsole && str_contains($consoleSlug, $candidateConsole)) {
                        $score += 20;
                    }

                    if ($score > $bestScore && $score >= 45) {
                        $bestScore = $score;
                        $bestUrl = $candidateUrl;
                    }
                }

                if ($bestUrl) {
                    $scrape = $scraper->scrape($bestUrl);
                    if (!empty($scrape['cover_url']) && !$scraper->isSiteLogoOrInvalid($scrape['cover_url'])) {
                        return $scrape['cover_url'];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Limpia un archivo de carátula local antiguo si existe
     */
    protected function cleanupLocalCoverFile(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        $parsedPath = parse_url($url, PHP_URL_PATH);
        if (!$parsedPath) {
            return;
        }

        $relativePath = ltrim($parsedPath, '/');
        $localPath = public_path($relativePath);
        if (file_exists($localPath) && is_file($localPath)) {
            @unlink($localPath);
        }
    }
}

