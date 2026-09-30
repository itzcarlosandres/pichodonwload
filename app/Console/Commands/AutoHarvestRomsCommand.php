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
                            {--provider= : Proveedor a rastrear: cdromance, romspedia, romsemu o all} 
                            {--console=all : Consola a explorar (ej: ps2), "random" para una aleatoria, o lista separada por comas (ej: ps2,psp,gba)} 
                            {--limit= : Límite de nuevos juegos a guardar en cola DRAFT} 
                            {--page= : Página específica para iniciar el rastreo o "random"} 
                            {--random-page : Iniciar en una página aleatoria (1-10) para explorar catálogo profundo} 
                            {--shuffle : Barajar los juegos encontrados para cosechar títulos variados y no siempre los primeros} 
                            {--dry-run : Solo rastrear y verificar sin guardar en base de datos}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Rastrea Romsemu, CDRomance y Romspedia de forma autónoma, descarta duplicados y encola juegos en DRAFT';

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
        'ps1' => 'playstation',
        'gamecube' => 'gamecube',
        'gc' => 'gamecube',
        'gba' => 'game-boy-advance',
        'game-boy-advance' => 'game-boy-advance',
        'nds' => 'nintendo-ds',
        'nintendo-ds' => 'nintendo-ds',
        '3ds' => 'nintendo-3ds',
        'nintendo-3ds' => 'nintendo-3ds',
        'snes' => 'super-nintendo',
        'super-nintendo' => 'super-nintendo',
        'n64' => 'nintendo-64',
        'nintendo-64' => 'nintendo-64',
        'dreamcast' => 'dreamcast',
        'dc' => 'dreamcast',
        'genesis' => 'sega-genesis',
        'sega-genesis' => 'sega-genesis',
        'switch' => 'nintendo-switch',
        'nintendo-switch' => 'nintendo-switch',
        'ps4' => 'playstation-4',
        'playstation-4' => 'playstation-4',
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
        $consoleInput = strtolower(trim($this->option('console') ?: 'all'));
        $limit = (int) ($this->option('limit') ?: config('roms.auto_harvest_limit', 10));
        $dryRun = (bool) $this->option('dry-run');
        $shouldShuffle = (bool) $this->option('shuffle');
        $userPageInput = $this->option('page');
        $isRandomPage = (bool) $this->option('random-page') || strtolower((string) $userPageInput) === 'random';

        $providers = $providerInput === 'all' 
            ? config('roms.providers', ['cdromance', 'romspedia', 'romsemu']) 
            : [$providerInput];

        $supportedConsoles = [
            'playstation-2', 
            'psp', 
            'playstation', 
            'gamecube', 
            'game-boy-advance', 
            'nintendo-ds', 
            'nintendo-3ds',
            'nintendo-switch',
            'super-nintendo', 
            'nintendo-64'
        ];

        // Resolución dinámica de consolas
        if ($consoleInput === 'random') {
            // Elegir 1 consola al azar de las soportadas
            $chosen = $supportedConsoles[array_rand($supportedConsoles)];
            $consolesToExplore = [$chosen];
            $isAll = false;
            $shouldShuffle = true;
        } elseif (str_contains($consoleInput, ',')) {
            // Múltiples consolas separadas por comas (ej. ps2,psp,gba)
            $parsedConsoles = array_map(function ($c) {
                $clean = trim($c);
                return $this->consoleMap[$clean] ?? $clean;
            }, explode(',', $consoleInput));
            
            $consolesToExplore = array_values(array_filter($parsedConsoles));
            shuffle($consolesToExplore);
            $isAll = count($consolesToExplore) > 1;
        } elseif ($consoleInput === 'all' || empty($consoleInput)) {
            $consolesToExplore = $supportedConsoles;
            shuffle($consolesToExplore);
            $isAll = true;
        } else {
            $mapped = $this->consoleMap[$consoleInput] ?? $consoleInput;
            $consolesToExplore = [$mapped];
            $isAll = false;
        }

        $this->info("🌾 Iniciando Auto-Cosecha de ROMs Rotativa (Piloto Automático)");
        $this->line("Consolas a rastrear: <info>" . implode(', ', $consolesToExplore) . "</info> | Límite objetivo: <comment>{$limit} juegos nuevos</comment>");
        if ($isRandomPage) {
            $this->line("🎲 Modo de catálogo profundo activo: Páginas aleatorias habilitadas.");
        }

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

                // Cálculo de página inicial
                if ($isRandomPage) {
                    $startPage = rand(1, 8);
                } elseif ($userPageInput && is_numeric($userPageInput)) {
                    $startPage = (int) $userPageInput;
                } else {
                    $startPage = ($provider === 'romspedia') ? rand(1, 6) : 1;
                }

                $maxPagesToScan = ($provider === 'romspedia') ? 4 : 3;

                for ($offset = 0; $offset < $maxPagesToScan; $offset++) {
                    $page = $startPage + $offset;
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

                    // Barajar candidatos si se solicitó o si está en modo aleatorio
                    $candidates = $browseResult['games'];
                    if ($shouldShuffle || $isRandomPage) {
                        shuffle($candidates);
                    }

                    foreach ($candidates as $candidate) {
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

                        $consoleName = $localConsole ? $localConsole->name : ($candidate['console_badge'] ?? $targetConsoleSlug);
                        $this->info("   ✨ <info>[NUEVO CANDIDATO]</info> {$title} ({$consoleName}) -> Extrayendo detalles...");

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

                            $harvestDl = $scrape['direct_download_url'] ?? ($scrape['download_url'] ?? null);
                            if ($harvestDl && (str_contains($harvestDl, 'romsemu.com') || str_contains($harvestDl, 'cdromance.org') || str_contains($harvestDl, 'romspedia.com'))) {
                                $harvestDl = null;
                            }

                            $cleanHarvestLinks = [];
                            if (!empty($scrape['download_links']) && is_array($scrape['download_links'])) {
                                foreach ($scrape['download_links'] as $link) {
                                    if (!empty($link['url']) && !str_contains($link['url'], 'romsemu.com') && !str_contains($link['url'], 'cdromance.org') && !str_contains($link['url'], 'romspedia.com')) {
                                        $cleanHarvestLinks[] = [
                                            'server' => !empty($link['server']) ? $link['server'] : '1Fichier',
                                            'url' => $link['url'],
                                            'name' => $link['name'] ?? null,
                                            'size' => $link['size'] ?? null,
                                        ];
                                    }
                                }
                            }

                            $game = Game::create([
                                'title' => $title,
                                'slug' => $slug,
                                'console_id' => $consoleId,
                                'cover_url' => $coverUrl,
                                'cover_thumb_url' => $thumbUrl,
                                'download_url' => $harvestDl,
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
                                'download_links' => !empty($cleanHarvestLinks) ? $cleanHarvestLinks : null,
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
