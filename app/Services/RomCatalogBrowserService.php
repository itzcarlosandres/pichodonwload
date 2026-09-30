<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Str;

class RomCatalogBrowserService
{
    /**
     * Mapeo de slugs de consolas locales a las URLs de proveedores externos
     */
    protected array $platformMap = [
        'romspedia' => [
            'psp' => 'playstation-portable',
            'playstation-2' => 'playstation-2',
            'playstation' => 'playstation-1',
            'gamecube' => 'nintendo-gamecube',
            'game-boy-advance' => 'gameboy-advance',
            'nintendo-ds' => 'nintendo-ds',
            'super-nintendo' => 'super-nintendo',
            'nintendo-64' => 'nintendo-64',
        ],
        'cdromance' => [
            'psp' => 'psp',
            'playstation-2' => 'ps2-iso',
            'playstation' => 'psx-iso',
            'gamecube' => 'gamecube',
            'game-boy-advance' => 'gba-roms',
            'nintendo-ds' => 'nds-roms',
            'super-nintendo' => 'snes-rom',
            'nintendo-64' => 'n64-roms',
        ],
        'romsemu' => [
            'nintendo-switch' => 'nintendo-switch',
            'nintendo-3ds' => 'nintendo-3ds',
            'playstation-4' => 'playstation-4',
            'playstation-vita' => 'playstation-vita',
            'psvita' => 'playstation-vita',
            'sega-sg-1000' => 'sega-sg-1000',
            'sega-32x' => 'sega-32x',
        ],
    ];

    protected ScraperSafetyService $safety;

    public function __construct(?ScraperSafetyService $safety = null)
    {
        $this->safety = $safety ?? app(ScraperSafetyService::class);
    }

    /**
     * Explora una página de catálogo según proveedor y consola
     */
    public function browse(string $provider, string $consoleSlug, int $page = 1): array
    {
        $provider = strtolower($provider);
        if ($provider === 'cdromance') {
            return $this->browseCdromance($consoleSlug, $page);
        } elseif ($provider === 'romsemu') {
            return $this->browseRomsemu($consoleSlug, $page);
        }

        return $this->browseRomspedia($consoleSlug, $page);
    }

    /**
     * Listado desde CDRomance.org
     */
    protected function browseCdromance(string $consoleSlug, int $page): array
    {
        $isAll = in_array(strtolower($consoleSlug), ['all', 'latest', '']);

        if ($isAll) {
            $url = $page > 1 
                ? "https://cdromance.org/page/{$page}/"
                : "https://cdromance.org/";
        } else {
            $platformPath = $this->platformMap['cdromance'][$consoleSlug] ?? 'psp';
            $url = $page > 1 
                ? "https://cdromance.org/{$platformPath}/page/{$page}/"
                : "https://cdromance.org/{$platformPath}/";
        }

        $fetchRes = $this->safety->safeFetch($url, 'cdromance', [], true, 900);
        if (!$fetchRes['success']) {
            return [
                'success' => false,
                'cooldown' => $fetchRes['cooldown'] ?? false,
                'remaining_seconds' => $fetchRes['remaining_seconds'] ?? null,
                'message' => $fetchRes['message'] ?? 'No se pudo conectar con el catálogo de CDRomance.',
                'games' => [],
                'current_page' => $page,
                'has_next' => false,
            ];
        }

        $html = $fetchRes['html'];

        $games = [];
        // Captura cada bloque de juego en CDRomance
        if (preg_match_all('/<div class="game-container">(.*?)<\/div>\s*<\/div>/is', $html, $blocks)) {
            foreach ($blocks[0] as $block) {
                // Enlace de la portada o ficha
                if (!preg_match('/<a class="cover-link" href="([^"]+)"/i', $block, $lm)) {
                    continue;
                }
                $gameUrl = $lm[1];

                // Ignorar guías o posts no-juego
                if (str_contains($gameUrl, '/guides/')) {
                    continue;
                }

                // Portada ultra-robusta
                $coverUrl = '';
                if (preg_match('/<img[^>]+(?:data-src|data-lazy-src|srcset|src)=["\']([^"\']+)["\']/i', $block, $im)) {
                    $coverUrl = trim($im[1]);
                    if (str_contains($coverUrl, ' ')) {
                        $parts = explode(' ', $coverUrl);
                        $coverUrl = $parts[0];
                    }
                }
                if ($coverUrl && str_starts_with($coverUrl, '//')) {
                    $coverUrl = 'https:' . $coverUrl;
                }

                // Descartar si es el logo del sitio o banner
                if (preg_match('/(?:cdr-logo|logo|banner|header|phoenix|avatar|favicon)/i', $coverUrl)) {
                    $coverUrl = '';
                }

                // Etiqueta de Consola visible
                $consoleTag = '';
                if (preg_match('/<div class="console[^"]*"[^>]*>([^<]+)<\/div>/i', $block, $cm)) {
                    $consoleTag = trim($cm[1]);
                }

                // Título
                $rawTitle = '';
                if (preg_match('/<div class="game-title">([^<]+)<\/div>/i', $block, $tm)) {
                    $rawTitle = html_entity_decode(strip_tags($tm[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                if (!$rawTitle) {
                    continue;
                }

                $cleanTitle = preg_replace('/\s+(?:PSP|PS2|PSX|GameCube|SNES|GBA|NDS)\s+(?:ISO|ROM|Game|Download).*$/i', '', $rawTitle);
                $cleanTitle = trim($cleanTitle);
                $slug = Str::slug($cleanTitle);

                // Determinar consola
                $detectedConsoleSlug = $consoleSlug;
                if ($isAll) {
                    if (preg_match('/cdromance\.org\/([a-z0-9\-]+)\//i', $gameUrl, $pm)) {
                        $detectedConsoleSlug = $this->reversePlatformSlug('cdromance', $pm[1]);
                    }
                }

                $games[] = [
                    'title' => $cleanTitle,
                    'slug' => $slug,
                    'url' => $gameUrl,
                    'cover_thumb' => $coverUrl,
                    'provider' => 'CDRomance',
                    'console_slug' => $detectedConsoleSlug,
                    'console_badge' => $consoleTag ?: strtoupper($detectedConsoleSlug),
                ];
            }
        }

        $games = $this->attachExistingStatus($games);

        $hasNext = str_contains($html, "/page/" . ($page + 1) . "/");

        return [
            'success' => true,
            'games' => $games,
            'current_page' => $page,
            'has_next' => $hasNext,
            'provider' => 'cdromance',
            'console' => $consoleSlug,
            'is_latest' => $isAll,
            'total_in_page' => count($games),
        ];
    }

    /**
     * Listado desde Romspedia.com
     */
    protected function browseRomspedia(string $consoleSlug, int $page): array
    {
        $isAll = in_array(strtolower($consoleSlug), ['all', 'latest', '']);

        if ($isAll) {
            // Rotar equitativamente por las consolas de Romspedia según la página solicitada
            $availableConsoles = ['nintendo-64', 'game-boy-advance', 'super-nintendo', 'playstation', 'nintendo-ds', 'gamecube', 'psp', 'playstation-2'];
            $targetConsole = $availableConsoles[($page - 1) % count($availableConsoles)];
            $platformPath = $this->platformMap['romspedia'][$targetConsole] ?? 'nintendo-64';
            $consoleSlug = $targetConsole;
            $subPage = intval(($page - 1) / count($availableConsoles)) + 1;
            $url = $subPage > 1
                ? "https://www.romspedia.com/roms/{$platformPath}/page/{$subPage}"
                : "https://www.romspedia.com/roms/{$platformPath}";
        } else {
            $platformPath = $this->platformMap['romspedia'][$consoleSlug] ?? 'playstation-portable';
            $url = $page > 1
                ? "https://www.romspedia.com/roms/{$platformPath}/page/{$page}"
                : "https://www.romspedia.com/roms/{$platformPath}";
        }

        $fetchRes = $this->safety->safeFetch($url, 'romspedia', [], true, 900);
        if (!$fetchRes['success']) {
            return [
                'success' => false,
                'cooldown' => $fetchRes['cooldown'] ?? false,
                'remaining_seconds' => $fetchRes['remaining_seconds'] ?? null,
                'message' => $fetchRes['message'] ?? 'No se pudo conectar con el catálogo de Romspedia.',
                'games' => [],
                'current_page' => $page,
                'has_next' => false,
            ];
        }

        $html = $fetchRes['html'];

        $games = [];
        // Romspedia lista los juegos con bloques class="roms-img" o enlaces directos /roms/{platform}/{game}
        $pattern = '/<div class="roms-img">\s*<a href="(\/roms\/[^"\/]+\/([^"\/]+))"[^>]*title="([^"]*)"/is';
        
        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $relativeUrl = $m[1];
                $gameSlug = $m[2];
                $rawTitle = html_entity_decode(strip_tags($m[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $cleanTitle = trim(preg_replace('/\s+ROM Download.*$/i', '', $rawTitle));
                $cleanTitle = trim(preg_replace('/\s+ROM$/i', '', $cleanTitle));

                $fullUrl = "https://www.romspedia.com" . $relativeUrl;

                // Extraer imagen miniatura del bloque correspondiente con consola correcta
                $consoleThumbSlug = match(strtolower($consoleSlug)) {
                    'nintendo-64', 'n64' => 'nintendo-64',
                    'super-nintendo', 'snes' => 'super-nintendo',
                    'game-boy-advance', 'gba' => 'game-boy-advance',
                    'nintendo-ds', 'nds' => 'nintendo-ds',
                    'playstation-2', 'ps2' => 'playstation-2',
                    'playstation', 'ps1', 'psx' => 'playstation',
                    'gamecube' => 'gamecube',
                    default => $consoleSlug,
                };
                $coverThumb = "https://static.romspedia.com/webp/roms/{$gameSlug}-cover.webp";
                $pos = strpos($html, $relativeUrl);
                if ($pos !== false) {
                    $snippet = substr($html, $pos, 1000);
                    // Priorizar la carátula activa real (ignora miniaturas comentadas)
                    if (preg_match('/(?:data-srcset|srcset|data-src|src)=["\']([^"\']*static\.romspedia\.com\/webp\/roms\/[^"\']*cover[^"\']*\.(?:webp|jpg|jpeg|png)[^"\']*)["\']/i', $snippet, $imgMatch) ||
                        preg_match('/(?:data-src|data-lazy-src|srcset|src)=["\']([^"\']+\.(?:webp|jpg|jpeg|png)[^"\']*)["\']/i', $snippet, $imgMatch)) {
                        $foundImg = trim($imgMatch[1]);
                        if (str_contains($foundImg, ' ')) {
                            $parts = explode(' ', $foundImg);
                            $foundImg = $parts[0];
                        }
                        if (str_starts_with($foundImg, '//')) {
                            $foundImg = 'https:' . $foundImg;
                        } elseif (!str_starts_with($foundImg, 'http')) {
                            $foundImg = 'https://www.romspedia.com' . (str_starts_with($foundImg, '/') ? '' : '/') . $foundImg;
                        }
                        if (!preg_match('/(?:logo|banner|avatar|favicon)/i', $foundImg)) {
                            $coverThumb = $foundImg;
                        }
                    }
                }

                $slug = Str::slug($cleanTitle);

                $games[] = [
                    'title' => $cleanTitle,
                    'slug' => $slug,
                    'url' => $fullUrl,
                    'cover_thumb' => $coverThumb,
                    'provider' => 'Romspedia',
                    'console_slug' => $consoleSlug,
                ];
            }
        }

        $games = $this->attachExistingStatus($games);

        $hasNext = str_contains($html, "/page/" . ($page + 1)) || str_contains($html, "page=" . ($page + 1)) || str_contains($html, "rel=\"next\"");

        return [
            'success' => true,
            'games' => $games,
            'current_page' => $page,
            'has_next' => $hasNext,
            'provider' => 'romspedia',
            'console' => $consoleSlug,
            'total_in_page' => count($games),
        ];
    }

    /**
     * Listado desde Romsemu.com
     */
    protected function browseRomsemu(string $consoleSlug, int $page): array
    {
        $isAll = in_array(strtolower($consoleSlug), ['all', 'latest', '']);

        if ($isAll) {
            $url = 'https://romsemu.com/';
        } else {
            $platformPath = $this->platformMap['romsemu'][$consoleSlug] ?? $consoleSlug;
            $url = $page > 1 
                ? "https://romsemu.com/roms/{$platformPath}/page/{$page}/"
                : "https://romsemu.com/roms/{$platformPath}/";
        }

        $fetchRes = $this->safety->safeFetch($url, 'romsemu', [], true, 900);
        if (!$fetchRes['success']) {
            return [
                'success' => false,
                'cooldown' => $fetchRes['cooldown'] ?? false,
                'remaining_seconds' => $fetchRes['remaining_seconds'] ?? null,
                'message' => $fetchRes['message'] ?? 'No se pudo conectar con el catálogo de Romsemu.',
                'games' => [],
                'current_page' => $page,
                'has_next' => false,
                'provider' => 'romsemu',
                'console' => $consoleSlug,
                'total_in_page' => 0,
            ];
        }

        $html = $fetchRes['html'];
        $games = [];

        if ($isAll) {
            // Extraer juegos de la sección 'Latest ROMs' de la portada
            if (preg_match('/Latest ROMs<\/h2>(.*?)(?:What are Video Game ROMs|Explore the Best Emulators|$)/is', $html, $sec)) {
                $sectionHtml = $sec[1];
                if (preg_match_all('/<div[^>]*class="[^"]*col-archive-rom[^"]*"[^>]*>.*?<a[^>]+href=["\'](https:\/\/romsemu\.com\/[^\/]+\/[^"\'\/]+\/)["\'][^>]*>.*?<img[^>]+(?:data-lazy-src|data-src|src)=["\']([^"\']+)["\'][^>]*>.*?<h3[^>]*>\s*([^<]+)\s*<\/h3>.*?<a[^>]+href=["\']https:\/\/romsemu\.com\/roms\/([^"\'\/]+)\/["\'][^>]*>([^<]+)<\/a>/is', $sectionHtml, $matches)) {
                    for ($i = 0; $i < count($matches[1]); $i++) {
                        $gameUrl = $matches[1][$i];
                        $coverUrl = $matches[2][$i];
                        $rawTitle = html_entity_decode(strip_tags($matches[3][$i]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $cleanTitle = trim(preg_replace('/\s+(?:Nintendo\s+Switch|Switch|ROM|ISO|NSP|XCI|Download|PS4|PlayStation\s+4|PS\s+Vita|PlayStation\s+Vita|Sega\s+32X|SG-1000).*$/i', '', $rawTitle));
                        $cSlug = $matches[4][$i];
                        $cName = trim(strip_tags($matches[5][$i]));

                        $games[] = [
                            'title' => $cleanTitle,
                            'slug' => Str::slug($cleanTitle),
                            'url' => $gameUrl,
                            'cover_thumb' => $coverUrl,
                            'provider' => 'Romsemu',
                            'console_slug' => $cSlug,
                            'console_badge' => strtoupper($cName),
                        ];
                    }
                }
            }
            $hasNext = false;
        } else {
            // Listado de consola específica
            if (preg_match_all('/<a[^>]+href=["\'](https:\/\/romsemu\.com\/[^\/]+\/[^"\'\/]+\/)["\'][^>]*>.*?<img[^>]+(?:data-lazy-src|data-src|src)=["\']([^"\']+)["\'][^>]*>.*?<span class="site-box-title">([^<]+)<\/span>/is', $html, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $gameUrl = $matches[1][$i];
                    $coverUrl = $matches[2][$i];
                    $rawTitle = html_entity_decode(strip_tags($matches[3][$i]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $cleanTitle = trim(preg_replace('/\s+(?:Nintendo\s+Switch|Switch|ROM|ISO|NSP|XCI|Download|PS4|PlayStation\s+4|PS\s+Vita|PlayStation\s+Vita|Sega\s+32X|SG-1000).*$/i', '', $rawTitle));
                    $slug = Str::slug($cleanTitle);

                    $games[] = [
                        'title' => $cleanTitle,
                        'slug' => $slug,
                        'url' => $gameUrl,
                        'cover_thumb' => $coverUrl,
                        'provider' => 'Romsemu',
                        'console_slug' => $consoleSlug,
                        'console_badge' => $this->getConsoleBadgeName($consoleSlug),
                    ];
                }
            }
            $hasNext = str_contains($html, "/page/" . ($page + 1) . "/");
        }

        $games = $this->attachExistingStatus($games);

        return [
            'success' => true,
            'games' => $games,
            'current_page' => $page,
            'has_next' => $hasNext,
            'provider' => 'romsemu',
            'console' => $consoleSlug,
            'total_in_page' => count($games),
        ];
    }

    /**
     * Nombre legible para el badge de la consola
     */
    protected function getConsoleBadgeName(string $slug): string
    {
        $names = [
            'nintendo-switch' => 'Nintendo Switch',
            'nintendo-3ds' => 'Nintendo 3DS',
            'playstation-4' => 'PlayStation 4',
            'playstation-vita' => 'PlayStation Vita',
            'psvita' => 'PlayStation Vita',
            'sega-sg-1000' => 'Sega SG-1000',
            'sega-32x' => 'Sega 32X',
            'psp' => 'PSP',
            'playstation-2' => 'PS2',
            'playstation' => 'PS1',
            'gamecube' => 'GameCube',
            'game-boy-advance' => 'GBA',
            'nintendo-ds' => 'NDS',
            'super-nintendo' => 'SNES',
            'nintendo-64' => 'N64',
        ];

        return strtoupper($names[$slug] ?? str_replace('-', ' ', $slug));
    }

    /**
     * Marca en lote los juegos que ya existen en el catálogo local con detección inteligente
     */
    protected function attachExistingStatus(array $games): array
    {
        if (empty($games)) {
            return [];
        }

        $allSlugs = [];
        $allTitles = [];

        foreach ($games as $g) {
            if (!empty($g['slug'])) {
                $allSlugs[] = $g['slug'];
            }
            if (!empty($g['title'])) {
                $allTitles[] = $g['title'];
            }
            if (!empty($g['url'])) {
                $path = trim(parse_url($g['url'], PHP_URL_PATH), '/');
                $slugCandidate = basename($path);
                if ($slugCandidate) {
                    $allSlugs[] = $slugCandidate;
                }
            }
        }

        $allSlugs = array_values(array_unique(array_filter($allSlugs)));
        $allTitles = array_values(array_unique(array_filter($allTitles)));

        $existing = Game::whereIn('slug', $allSlugs)
            ->orWhereIn('title', $allTitles)
            ->get(['id', 'title', 'slug', 'download_url']);

        foreach ($games as &$g) {
            $gPath = !empty($g['url']) ? basename(trim(parse_url($g['url'], PHP_URL_PATH), '/')) : '';

            $match = $existing->first(function ($dbGame) use ($g, $gPath) {
                if ($dbGame->slug === $g['slug'] || $dbGame->title === $g['title']) {
                    return true;
                }
                if ($gPath && ($dbGame->slug === $gPath || str_contains($dbGame->slug, $gPath))) {
                    return true;
                }
                if ($gPath && !empty($dbGame->download_url) && str_contains($dbGame->download_url, $gPath)) {
                    return true;
                }
                return false;
            });

            if ($match) {
                $g['already_imported'] = true;
                $g['imported_id'] = $match->id;
                $g['edit_url'] = route('admin.games.edit', $match->id);
                $g['view_url'] = route('game.show', $match->slug);
            } else {
                $g['already_imported'] = false;
                $g['imported_id'] = null;
                $g['edit_url'] = null;
                $g['view_url'] = null;
            }
        }

        return $games;
    }

    /**
     * Mapea un segmento de ruta de la URL a un slug de consola local
     */
    protected function reversePlatformSlug(string $provider, string $pathSegment): string
    {
        $pathSegment = strtolower(trim($pathSegment));
        $map = array_flip($this->platformMap[$provider] ?? []);

        if (isset($map[$pathSegment])) {
            return $map[$pathSegment];
        }

        // Casos adicionales frecuentes de CDRomance
        $extraMap = [
            'ps2-iso' => 'playstation-2',
            'psx-iso' => 'playstation',
            'dc-iso' => 'dreamcast',
            'gba-roms' => 'game-boy-advance',
            'nds-roms' => 'nintendo-ds',
            'snes-rom' => 'super-nintendo',
            'nes-rom' => 'nes',
            'n64-roms' => 'nintendo-64',
            'sega_genesis_roms' => 'sega-genesis',
            'windows' => 'pc',
        ];

        return $extraMap[$pathSegment] ?? $pathSegment;
    }
}
