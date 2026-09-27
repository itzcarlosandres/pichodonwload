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
            'playstation' => 'playstation',
            'gamecube' => 'gamecube',
            'game-boy-advance' => 'game-boy-advance',
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

                // Portada
                $coverUrl = '';
                if (preg_match('/<img[^>]+(?:src|data-src)="([^"]+)"/i', $block, $im)) {
                    $coverUrl = $im[1];
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
        $platformPath = $this->platformMap['romspedia'][$consoleSlug] ?? 'playstation-portable';
        $url = $page > 1
            ? "https://www.romspedia.com/roms/{$platformPath}?page={$page}"
            : "https://www.romspedia.com/roms/{$platformPath}";

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

                // Extraer imagen miniatura del bloque correspondiente
                $coverThumb = "https://static.romspedia.com/webp/roms/thumbs/{$gameSlug}-psp-thumb-250x140.webp";
                $pos = strpos($html, $relativeUrl);
                if ($pos !== false) {
                    $snippet = substr($html, $pos, 600);
                    if (preg_match('/(?:src|srcset)="([^">]+\.(?:webp|jpg|jpeg|png))"/i', $snippet, $imgMatch)) {
                        $coverThumb = $imgMatch[1];
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

        $hasNext = str_contains($html, "page=" . ($page + 1)) || str_contains($html, "rel=\"next\"");

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
