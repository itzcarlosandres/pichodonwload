<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\Console;
use App\Services\RomCatalogBrowserService;
use App\Services\RomScraperService;
use App\Services\ImageOptimizationService;
use App\Services\AiContentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AutoHarvestRomsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roms:auto-harvest 
                            {--provider= : Proveedor a rastrear: cdromance, romspedia o all} 
                            {--console=all : Consola a explorar o all} 
                            {--limit= : Límite de nuevos juegos a guardar en cola DRAFT} 
                            {--dry-run : Solo rastrear y verificar sin guardar en base de datos}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Rastrea CDRomance y Romspedia de forma autónoma, descarta duplicados y encola juegos en DRAFT';

    /**
     * Normalización de slugs de consola a IDs locales
     */
    protected array $consoleMap = [
        'psp' => 'psp',
        'playstation-portable' => 'psp',
        'ps2' => 'playstation-2',
        'playstation-2' => 'playstation-2',
        'psx' => 'playstation',
        'playstation' => 'playstation',
        'gamecube' => 'gamecube',
        'gba' => 'game-boy-advance',
        'game-boy-advance' => 'game-boy-advance',
        'nds' => 'nintendo-ds',
        'nintendo-ds' => 'nintendo-ds',
        'snes' => 'snes',
        'super-nintendo' => 'snes',
        'n64' => 'nintendo-64',
        'nintendo-64' => 'nintendo-64',
        'dreamcast' => 'dreamcast',
        'genesis' => 'sega-genesis',
        'sega-genesis' => 'sega-genesis',
    ];

    public function handle(
        RomCatalogBrowserService $browserService,
        RomScraperService $scraperService,
        ImageOptimizationService $imageService,
        AiContentService $aiService,
        \App\Services\FranchiseSyncService $franchiseService,
        \App\Services\CategorySyncService $categoryService
    ): int {
        $providerInput = strtolower($this->option('provider') ?: 'all');
        $consoleSlug = $this->option('console') ?: 'all';
        $limit = (int) ($this->option('limit') ?: config('roms.auto_harvest_limit', 10));
        $dryRun = (bool) $this->option('dry-run');

        $providers = $providerInput === 'all' 
            ? config('roms.providers', ['cdromance', 'romspedia']) 
            : [$providerInput];

        $isAll = in_array(strtolower($consoleSlug), ['all', '']);
        $consolesToExplore = $isAll 
            ? ['nintendo-64', 'game-boy-advance', 'super-nintendo', 'playstation', 'nintendo-ds', 'gamecube', 'psp', 'playstation-2']
            : [$consoleSlug];

        // Barajar aleatoriamente para variar el punto de partida en cada ejecución del cron
        if ($isAll) {
            shuffle($consolesToExplore);
        }

        $this->info("🌾 Iniciando Auto-Cosecha de ROMs Rotativa (Piloto Automático)");
        $this->line("Consolas a rastrear: " . implode(', ', $consolesToExplore) . " | Límite objetivo: {$limit} juegos nuevos");

        $totalHarvested = 0;
        $totalSkippedDuplicates = 0;

        // Cuota máxima por consola en esta ejecución para forzar variedad equitativa
        $maxPerConsole = $isAll ? max(1, (int) ceil($limit / count($consolesToExplore))) : $limit;

        foreach ($consolesToExplore as $consoleIndex => $targetConsole) {
            if ($totalHarvested >= $limit) {
                break;
            }

            $harvestedForConsole = 0;

            // Alternar equitativamente el proveedor inicial para que Romspedia y CDRomance alimenten la cola por igual
            $providersForConsole = $providers;
            if ($consoleIndex % 2 !== 0) {
                $providersForConsole = array_reverse($providersForConsole);
            }

            foreach ($providersForConsole as $provider) {
                if ($totalHarvested >= $limit || $harvestedForConsole >= $maxPerConsole) {
                    break;
                }

                $this->newLine();
                $this->comment("📡 Explorando [{$targetConsole}] en: " . strtoupper($provider));

                for ($page = 1; $page <= 2; $page++) {
                    if ($totalHarvested >= $limit || $harvestedForConsole >= $maxPerConsole) {
                        break;
                    }

                    $this->line("   📄 Leyendo página {$page} de {$provider} ({$targetConsole})...");
                    try {
                        $browseResult = $browserService->browse($provider, $targetConsole, $page);
                    } catch (\Throwable $e) {
                        $this->error("   ❌ Error al conectar con {$provider}: " . $e->getMessage());
                        break;
                    }

                    if (!$browseResult['success'] || empty($browseResult['games'])) {
                        $this->warn("   ⚠️ Sin resultados en {$provider} ({$targetConsole}) pág {$page}.");
                        break;
                    }

                    foreach ($browseResult['games'] as $candidate) {
                        if ($totalHarvested >= $limit || $harvestedForConsole >= $maxPerConsole) {
                            break;
                        }

                        $title = $candidate['title'] ?? '';
                        $sourceUrl = $candidate['url'] ?? '';
                        $detectedSlug = $candidate['console_slug'] ?? $targetConsole;

                        // Mapear consola local
                        $targetConsoleSlug = $this->consoleMap[$detectedSlug] ?? $detectedSlug;
                        $localConsole = Console::where('slug', $targetConsoleSlug)->first()
                            ?? Console::where('slug', 'psp')->first();

                        $consoleId = $localConsole ? $localConsole->id : 1;

                        // VERIFICADOR ESTRICTO ANTI-DUPLICADOS
                        $duplicate = Game::findDuplicate($title, $consoleId, $sourceUrl);
                        if ($duplicate) {
                            $totalSkippedDuplicates++;
                            $this->line("   🚫 <comment>[DUPLICADO OMITIDO]</comment> {$title} (Ya existe en BD #{$duplicate->id} - {$duplicate->status})");
                            continue;
                        }

                        $this->info("   ✨ <info>[NUEVO CANDIDATO]</info> {$title} ({$localConsole->name}) -> Extrayendo detalles...");

                        if ($dryRun) {
                            $this->comment("      [DRY-RUN] Simulado: se habría guardado en cola DRAFT.");
                            $totalHarvested++;
                            $harvestedForConsole++;
                            continue;
                        }

                        // Scrapear ficha completa
                        try {
                            $scrape = $scraperService->scrape($sourceUrl);
                            if (!$scrape['success']) {
                                $this->warn("      ⚠️ Falló extracción detallada para {$title}: " . ($scrape['message'] ?? ''));
                                continue;
                            }

                            // Optimización de carátula a WebP local con bypass anti-bloqueo
                            $coverUrl = $scrape['cover_url'] ?? ($candidate['cover_thumb'] ?? null);
                            if (!empty($coverUrl) && $scraperService->isSiteLogoOrInvalid($coverUrl)) {
                                $coverUrl = null;
                            }
                            if (empty($coverUrl)) {
                                $coverUrl = $scraperService->searchRomspediaCover($title, $localConsole->slug ?? '');
                            }
                            if (!empty($coverUrl) && $scraperService->isSiteLogoOrInvalid($coverUrl)) {
                                $coverUrl = null;
                            }
                            $thumbUrl = null;

                            if (!empty($coverUrl)) {
                                try {
                                    $tempPath = tempnam(sys_get_temp_dir(), 'rom_cover_');
                                    $ch = curl_init($coverUrl);
                                    $fp = fopen($tempPath, 'wb');
                                    $host = parse_url($coverUrl, PHP_URL_HOST) ?? 'www.romspedia.com';
                                    $referer = (parse_url($coverUrl, PHP_URL_SCHEME) ?? 'https') . '://' . $host . '/';
                                    curl_setopt_array($ch, [
                                        CURLOPT_FILE => $fp,
                                        CURLOPT_HEADER => false,
                                        CURLOPT_TIMEOUT => 20,
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
                                        $coverUrl = $processed['url'];
                                        $thumbUrl = $processed['thumb_url'];
                                    }
                                } catch (\Throwable $e) {
                                    // Fallback a URL remota si falla procesamiento WebP
                                }
                            }

                            // Generación de descripción y SEO con IA para no duplicar texto
                            $consoleName = $localConsole ? $localConsole->name : 'Retro Console';
                            $context = [
                                'release_year' => $scrape['release_year'] ?? null,
                                'region' => $scrape['region'] ?? 'USA',
                                'languages' => $scrape['languages'] ?? 'English',
                                'publisher' => $scrape['publisher'] ?? null,
                                'developer' => $scrape['developer'] ?? null,
                            ];

                            $description = 'Pendiente de revisión.';
                            $metaTitle = null;
                            $metaDescription = null;

                            try {
                                $aiRich = $aiService->generateRichDescription($title, $consoleName, $context);
                                if (!empty($aiRich['description'])) {
                                    $description = $aiRich['description'];
                                }

                                $aiSeo = $aiService->generateSeo($title, $consoleName, $context);
                                if (!empty($aiSeo['meta_title'])) $metaTitle = $aiSeo['meta_title'];
                                if (!empty($aiSeo['meta_description'])) $metaDescription = $aiSeo['meta_description'];
                            } catch (\Throwable $e) {
                                // Ignorar error de IA y mantener contenido base
                            }

                            // Crear registro seguro en cola DRAFT
                            $slug = Str::slug($title);
                            $slugCount = Game::where('slug', 'LIKE', "{$slug}%")->count();
                            if ($slugCount > 0) {
                                $slug .= '-' . ($slugCount + 1);
                            }

                            $game = Game::create([
                                'title' => $title,
                                'slug' => $slug,
                                'console_id' => $consoleId,
                                'cover_url' => $coverUrl,
                                'cover_thumb_url' => $thumbUrl,
                                'download_url' => $scrape['download_url'] ?? $sourceUrl,
                                'file_size' => $scrape['file_size'] ?? '1.0 GB',
                                'file_format' => $scrape['file_format'] ?? 'ZIP',
                                'release_year' => !empty($scrape['release_year']) ? (int)$scrape['release_year'] : null,
                                'region' => $scrape['region'] ?? 'USA',
                                'languages' => $scrape['languages'] ?? 'English',
                                'publisher' => $scrape['publisher'] ?? null,
                                'developer' => $scrape['developer'] ?? null,
                                'serial' => $scrape['serial'] ?? null,
                                'description' => $description,
                                'meta_title' => $metaTitle,
                                'meta_description' => $metaDescription,
                                'status' => 'DRAFT', // EN COLA PARA EL DRIP PUBLISHER
                                'download_count' => 0,
                                'views_count' => 0,
                            ]);

                            // Vincular categorías y géneros detectados/creados automáticamente
                            $assignedCats = $categoryService->syncGame($game, false, $scrape['category_ids'] ?? []);
                            if (!empty($assignedCats)) {
                                $this->line("      🏷️ <fg=cyan>[CATEGORÍAS AUTO-ASIGNADAS]</> " . implode(', ', $assignedCats));
                            }

                            // Vincular automáticamente a Sagas / Franquicias correspondientes
                            $matchedFranchises = $franchiseService->syncGame($game);
                            if (!empty($matchedFranchises)) {
                                $this->line("      ✨ <fg=yellow>[SAGA DETECTADA]</> Vinculado a " . count($matchedFranchises) . " colección(es)");
                            }

                            $totalHarvested++;
                            $harvestedForConsole++;
                            $this->info("      💾 Guardado en cola DRAFT: {$game->title} [{$localConsole->name}] (ID #{$game->id})");

                            // Pausa de cortesía para proteger el servidor y el origen
                            sleep(2);

                        } catch (\Throwable $e) {
                            $this->error("      ❌ Error guardando juego {$title}: " . $e->getMessage());
                        }
                    }

                    if (!$browseResult['has_next']) {
                        break;
                    }
                }
            }
        }

        $this->newLine();
        $this->info("🏁 Resumen de la Auto-Cosecha:");
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Nuevos juegos encolados en DRAFT', $totalHarvested],
                ['Duplicados detectados y omitidos', $totalSkippedDuplicates],
                ['Total acumulado en cola DRAFT esperando publicación', Game::where('status', 'DRAFT')->count()],
            ]
        );

        return self::SUCCESS;
    }
}
