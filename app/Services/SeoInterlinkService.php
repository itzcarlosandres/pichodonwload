<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Console;
use App\Models\Category;
use App\Models\Franchise;
use App\Models\Emulator;

class SeoInterlinkService
{
    /**
     * Maximum number of internal links to inject into a single description
     */
    protected int $maxLinksPerDescription = 4;

    /**
     * Automatically enriches rendered HTML by linking relevant keywords
     * to internal categories, consoles, sagas, emulators, and rankings.
     * 
     * Uses a tokenized segmentation approach to ensure:
     * 1. Keywords are NEVER matched inside existing or newly created <a> tags.
     * 2. Keywords are NEVER matched inside HTML attributes (href, class, title, etc.).
     * 3. Target URLs are deduplicated (each destination URL is linked at most once).
     * 4. URLs and paths inside plain text are never corrupted.
     */
    public function interlinkDescription(string $html, Game $game): string
    {
        $keywords = $this->buildKeywordMap($game);

        if (empty($keywords) || empty(trim($html))) {
            return $html;
        }

        // Tokenize HTML into segments: 'text', 'tag', 'protected_link'
        [$segments, $existingUrls] = $this->tokenizeHtml($html);

        $usedUrls = $existingUrls;
        $totalLinked = 0;

        foreach ($keywords as $kw) {
            if ($totalLinked >= $this->maxLinksPerDescription) {
                break;
            }

            $normalizedUrl = rtrim(strtolower($kw['url']), '/');
            if (isset($usedUrls[$normalizedUrl])) {
                continue;
            }

            $term = trim($kw['term']);
            if (mb_strlen($term) < 3) {
                continue;
            }

            $escapedTerm = preg_quote($term, '/');
            // Ensure match is a standalone word, not part of a URL, slug, or filename
            $pattern = '/(?<![\/\-_@#\w])(' . $escapedTerm . ')(?![\/\-_@#\w])/iu';

            $newSegments = [];
            $replaced = false;

            foreach ($segments as $segment) {
                if ($replaced || $segment['type'] !== 'text') {
                    $newSegments[] = $segment;
                    continue;
                }

                if (preg_match($pattern, $segment['content'], $match, PREG_OFFSET_CAPTURE)) {
                    $matchText = $match[1][0];
                    $matchOffset = $match[1][1];

                    $beforeMatch = substr($segment['content'], 0, $matchOffset);
                    $afterMatch = substr($segment['content'], $matchOffset + strlen($matchText));

                    // Guard against matches inside URLs in plain text (e.g., https://site.com/psp/game)
                    if (preg_match('/(?:https?:\/\/[^\s<>"]*|\/[a-z0-9\-_.]+)$/i', $beforeMatch)) {
                        $newSegments[] = $segment;
                        continue;
                    }

                    // Build protected link tag
                    $anchor = sprintf(
                        '<a href="%s" class="text-[#CE2D2D] hover:underline font-semibold" title="%s">%s</a>',
                        htmlspecialchars($kw['url'], ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($kw['title'], ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($matchText, ENT_QUOTES, 'UTF-8')
                    );

                    if ($beforeMatch !== '') {
                        $newSegments[] = ['type' => 'text', 'content' => $beforeMatch];
                    }

                    $newSegments[] = ['type' => 'protected_link', 'content' => $anchor];

                    if ($afterMatch !== '') {
                        $newSegments[] = ['type' => 'text', 'content' => $afterMatch];
                    }

                    $usedUrls[$normalizedUrl] = true;
                    $totalLinked++;
                    $replaced = true;
                } else {
                    $newSegments[] = $segment;
                }
            }

            $segments = $newSegments;
        }

        // Reconstruct safe HTML
        $result = '';
        foreach ($segments as $segment) {
            $result .= $segment['content'];
        }

        return $result;
    }

    /**
     * Splits HTML into text nodes, HTML tags, and protected links (<a>...</a>).
     * Returns the array of segments and an array of existing destination URLs.
     */
    protected function tokenizeHtml(string $html): array
    {
        $links = [];
        $existingUrls = [];
        $linkPlaceholderPrefix = '___PLINK_' . uniqid() . '_';

        // Find existing <a>...</a> tags and isolate them with unique tokens
        $htmlWithPlaceholders = preg_replace_callback('/<a\b[^>]*>.*?<\/a>/is', function ($matches) use (&$links, &$existingUrls, $linkPlaceholderPrefix) {
            $index = count($links);
            $links[$index] = $matches[0];
            if (preg_match('/href=["\']([^"\']+)["\']/i', $matches[0], $hm)) {
                $existingUrls[rtrim(strtolower($hm[1]), '/')] = true;
            }
            return $linkPlaceholderPrefix . $index . '___';
        }, $html);

        // Split by all other HTML tags (<...>)
        $rawChunks = preg_split('/(<[^>]+>)/', $htmlWithPlaceholders, -1, PREG_SPLIT_DELIM_CAPTURE);
        $segments = [];

        foreach ($rawChunks as $chunk) {
            if ($chunk === '') {
                continue;
            }

            if (str_starts_with($chunk, '<') && str_ends_with($chunk, '>')) {
                $segments[] = ['type' => 'tag', 'content' => $chunk];
            } else {
                if (str_contains($chunk, $linkPlaceholderPrefix)) {
                    $subParts = preg_split('/(' . preg_quote($linkPlaceholderPrefix, '/') . '\d+___)/', $chunk, -1, PREG_SPLIT_DELIM_CAPTURE);
                    foreach ($subParts as $subPart) {
                        if ($subPart === '') {
                            continue;
                        }
                        if (preg_match('/^' . preg_quote($linkPlaceholderPrefix, '/') . '(\d+)___$/', $subPart, $m)) {
                            $linkIndex = (int) $m[1];
                            $segments[] = ['type' => 'protected_link', 'content' => $links[$linkIndex]];
                        } else {
                            $segments[] = ['type' => 'text', 'content' => $subPart];
                        }
                    }
                } else {
                    $segments[] = ['type' => 'text', 'content' => $chunk];
                }
            }
        }

        return [$segments, $existingUrls];
    }

    /**
     * Build the keyword dictionary specifically tailored for this game.
     */
    protected function buildKeywordMap(Game $game): array
    {
        $map = [];

        // 1. Console interlink
        if ($game->console) {
            $console = $game->console;
            $map[] = [
                'term' => $console->name,
                'url' => route('consoles.show', $console->slug),
                'title' => "Ver catálogo completo de juegos para {$console->name}",
            ];

            if (!empty($console->short_name) && mb_strlen($console->short_name) >= 3 && strcasecmp($console->short_name, $console->name) !== 0) {
                $map[] = [
                    'term' => $console->short_name,
                    'url' => route('consoles.show', $console->slug),
                    'title' => "Catálogo de ROMs para {$console->name}",
                ];
            }
        }

        // 2. Sagas / Franchises interlink
        if ($game->franchises && $game->franchises->isNotEmpty()) {
            foreach ($game->franchises as $franchise) {
                if (mb_strlen($franchise->name) >= 3) {
                    $map[] = [
                        'term' => $franchise->name,
                        'url' => route('collections.show', $franchise->slug),
                        'title' => "Colección completa de la saga {$franchise->name}",
                    ];
                }
            }
        }

        // 3. Categories / Genres interlink
        if ($game->categories && $game->categories->isNotEmpty()) {
            foreach ($game->categories as $category) {
                if (mb_strlen($category->name) >= 3) {
                    $map[] = [
                        'term' => $category->name,
                        'url' => route('category.show', $category->slug),
                        'title' => "Explorar videojuegos del género {$category->name}",
                    ];

                    // Check sub-names if category has '&' (e.g. "Acción & Aventura" -> "Acción", "Aventura")
                    if (str_contains($category->name, '&')) {
                        $parts = array_map('trim', explode('&', $category->name));
                        foreach ($parts as $part) {
                            if (mb_strlen($part) >= 4) {
                                $map[] = [
                                    'term' => $part,
                                    'url' => route('category.show', $category->slug),
                                    'title' => "Videojuegos de {$category->name}",
                                ];
                            }
                        }
                    }
                }
            }
        }

        // 4. Rankings for this console
        if ($game->console) {
            $map[] = [
                'term' => 'Top 25',
                'url' => route('rankings') . '?console=' . $game->console->slug,
                'title' => "Ranking Top 25 juegos más jugados de {$game->console->name}",
            ];
        }

        // 5. Emulators / BIOS hubs
        $map[] = [
            'term' => 'RetroArch',
            'url' => route('emulators'),
            'title' => 'Descargar emuladores oficiales recomendados',
        ];
        $map[] = [
            'term' => 'BIOS',
            'url' => route('bios'),
            'title' => 'Descargar archivos de firmware y BIOS del sistema',
        ];

        // Sort keywords by length descending so longer terms match first (e.g. "Nintendo DS" before "Nintendo")
        usort($map, fn($a, $b) => mb_strlen($b['term']) <=> mb_strlen($a['term']));

        return $map;
    }
}
