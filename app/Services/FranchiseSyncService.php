<?php

namespace App\Services;

use App\Models\Franchise;
use App\Models\Game;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FranchiseSyncService
{
    /**
     * Cache en memoria de todas las franquicias activas con sus términos procesados
     *
     * @var Collection|null
     */
    protected ?Collection $activeFranchises = null;

    /**
     * Obtiene y prepara las franquicias activas
     */
    public function getActiveFranchises(): Collection
    {
        if ($this->activeFranchises === null) {
            $this->activeFranchises = Franchise::where('is_active', true)
                ->orderBy('order')
                ->get();
        }

        return $this->activeFranchises;
    }

    /**
     * Determina qué franquicias coinciden con un juego dado su título y metadatos
     *
     * @param Game $game
     * @return Collection<int, Franchise>
     */
    public function matchFranchisesForGame(Game $game): Collection
    {
        $franchises = $this->getActiveFranchises();
        $matched = collect();

        $title = $game->title;
        $slug = $game->slug;
        $developer = $game->developer ?? '';

        foreach ($franchises as $franchise) {
            if ($this->isMatch($franchise, $title, $slug, $developer)) {
                $matched->push($franchise);
            }
        }

        return $matched;
    }

    /**
     * Verifica si un juego coincide con los términos y nombre de una franquicia
     */
    protected function isMatch(Franchise $franchise, string $title, string $slug, string $developer): bool
    {
        // 1. Términos a evaluar (nombre de la saga + array search_terms)
        $terms = is_array($franchise->search_terms) ? $franchise->search_terms : [];
        if (!in_array($franchise->name, $terms)) {
            array_unshift($terms, $franchise->name);
        }

        $titleNormalized = $this->normalizeString($title);
        $slugNormalized = str_replace('-', ' ', $this->normalizeString($slug));
        $devNormalized = $this->normalizeString($developer);

        foreach ($terms as $term) {
            $termClean = trim($term);
            if (empty($termClean) || mb_strlen($termClean) < 2) {
                continue;
            }

            $termNorm = $this->normalizeString($termClean);

            // Casos especiales con acrónimos cortos (ej. GTA, RE, DBZ, NFS)
            if (mb_strlen($termNorm) <= 4 && preg_match('/^[a-z0-9]+$/i', $termNorm)) {
                $pattern = '/\b' . preg_quote($termNorm, '/') . '\b/i';
                if (preg_match($pattern, $titleNormalized) || preg_match($pattern, $slugNormalized)) {
                    return true;
                }
                continue;
            }

            // Expresión regular con delimitador de palabras para evitar falsos positivos
            // (Ej: "link" no debe coincidir con "blink", "mario" no debe coincidir con "marionette")
            $pattern = '/\b' . preg_quote($termNorm, '/') . '\b/i';
            if (preg_match($pattern, $titleNormalized) || preg_match($pattern, $slugNormalized)) {
                return true;
            }

            // Si el término tiene más de 6 letras, permitir coincidencia de frase completa
            if (mb_strlen($termNorm) > 6 && (str_contains($titleNormalized, $termNorm) || str_contains($slugNormalized, $termNorm))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normaliza un string para búsqueda insensible a acentos y caracteres especiales
     */
    protected function normalizeString(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        // Reemplazar acentos y caracteres especiales
        $unwanted = [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ü'=>'u', 'ñ'=>'n',
            'à'=>'a', 'è'=>'e', 'ì'=>'i', 'ò'=>'o', 'ù'=>'u',
            'â'=>'a', 'ê'=>'e', 'î'=>'i', 'ô'=>'o', 'û'=>'u',
            ':'=>' ', '-'=>' ', '_'=>' ', '\''=>'', '"'=>'', '.'=>' ', ','=>' '
        ];
        $text = strtr($text, $unwanted);
        // Colapsar espacios múltiples
        return preg_replace('/\s+/', ' ', $text);
    }

    /**
     * Sincroniza las sagas detectadas para un solo juego
     *
     * @param Game $game
     * @param bool $overrideManual Si es true, reemplaza vínculos existentes; si es false, los combina
     * @return array IDs de franquicias vinculadas
     */
    public function syncGame(Game $game, bool $overrideManual = false): array
    {
        $matched = $this->matchFranchisesForGame($game);
        $matchedIds = $matched->pluck('id')->toArray();

        if (empty($matchedIds)) {
            return [];
        }

        if ($overrideManual) {
            $game->franchises()->sync($matchedIds);
        } else {
            // Combinar con franquicias que el admin ya haya vinculado manualmente
            $existingIds = $game->franchises()->pluck('franchises.id')->toArray();
            $allIds = array_values(array_unique(array_merge($existingIds, $matchedIds)));
            $game->franchises()->sync($allIds);
        }

        return $matchedIds;
    }

    /**
     * Sincroniza todas las franquicias con todos los juegos del catálogo
     *
     * @param int|null $franchiseId Opcional: filtrar por una franquicia en particular
     * @param bool $dryRun Si es true, no modifica la base de datos
     * @return array Resumen detallado de la sincronización
     */
    public function syncAllFranchises(?int $franchiseId = null, bool $dryRun = false): array
    {
        $gamesQuery = Game::query();
        $franchisesQuery = Franchise::where('is_active', true);

        if ($franchiseId) {
            $franchisesQuery->where('id', $franchiseId);
        }

        $franchises = $franchisesQuery->get();
        $totalGames = $gamesQuery->count();

        $stats = [
            'total_games_scanned' => $totalGames,
            'total_franchises' => $franchises->count(),
            'links_created' => 0,
            'franchise_details' => [],
        ];

        foreach ($franchises as $franchise) {
            $matchedGameIds = [];

            // Buscar juegos que coincidan con esta franquicia
            $games = Game::select('id', 'title', 'slug', 'developer')->get();

            foreach ($games as $game) {
                if ($this->isMatch($franchise, $game->title, $game->slug, $game->developer ?? '')) {
                    $matchedGameIds[] = $game->id;
                }
            }

            $currentCount = $franchise->games()->count();
            $newLinksCount = count($matchedGameIds);

            if (!$dryRun && !empty($matchedGameIds)) {
                // Combinar con los existentes sin duplicar
                $existing = $franchise->games()->pluck('games.id')->toArray();
                $finalIds = array_values(array_unique(array_merge($existing, $matchedGameIds)));
                $franchise->games()->sync($finalIds);
            }

            $stats['links_created'] += max(0, $newLinksCount - $currentCount);
            $stats['franchise_details'][$franchise->name] = [
                'id' => $franchise->id,
                'slug' => $franchise->slug,
                'previous_count' => $currentCount,
                'matched_count' => $newLinksCount,
            ];
        }

        return $stats;
    }
}
