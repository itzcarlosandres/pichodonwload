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
     * Automatically enriches rendered HTML by linking relevant keywords
     * to internal categories, consoles, sagas, emulators, and rankings.
     */
    public function interlinkDescription(string $html, Game $game): string
    {
        $keywords = $this->buildKeywordMap($game);

        if (empty($keywords)) {
            return $html;
        }

        // Split by HTML tags to only replace inside plain text nodes
        $chunks = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $inAnchor = false;

        foreach ($keywords as $kw) {
            $term = preg_quote($kw['term'], '/');
            $pattern = '/\b(' . $term . ')\b/iu';
            $replaced = false;

            for ($i = 0; $i < count($chunks); $i++) {
                if (preg_match('/^<a\b/i', $chunks[$i])) {
                    $inAnchor = true;
                    continue;
                }
                if (preg_match('/^<\/a>/i', $chunks[$i])) {
                    $inAnchor = false;
                    continue;
                }
                if ($inAnchor || strpos($chunks[$i], '<') === 0) {
                    continue;
                }

                // Replace only the first occurrence in the entire text
                if (!$replaced && preg_match($pattern, $chunks[$i])) {
                    $replacement = '<a href="' . htmlspecialchars($kw['url'], ENT_QUOTES, 'UTF-8') . '" class="text-[#CE2D2D] hover:underline font-semibold" title="' . htmlspecialchars($kw['title'], ENT_QUOTES, 'UTF-8') . '">$1</a>';
                    $chunks[$i] = preg_replace($pattern, $replacement, $chunks[$i], 1);
                    $replaced = true;
                    break;
                }
            }
        }

        return implode('', $chunks);
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
                $map[] = [
                    'term' => $franchise->name,
                    'url' => route('collections.show', $franchise->slug),
                    'title' => "Colección completa de la saga {$franchise->name}",
                ];
            }
        }

        // 3. Categories / Genres interlink
        if ($game->categories && $game->categories->isNotEmpty()) {
            foreach ($game->categories as $category) {
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
