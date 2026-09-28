<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Game;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategorySyncService
{
    /**
     * Sincroniza categorías para un juego si no tiene ninguna asignada o si se fuerza la reevaluación.
     *
     * @param Game $game
     * @param bool $force
     * @param array $preferredCategoryIds
     * @return array
     */
    public function syncGame(Game $game, bool $force = false, array $preferredCategoryIds = []): array
    {
        if (!$force && $game->categories()->exists()) {
            return $game->categories->pluck('name')->toArray();
        }

        // 1. Si se pasan IDs preferentes válidos (por ejemplo desde el scraper)
        $categoryIds = array_filter($preferredCategoryIds);

        // 2. Si no hay IDs preferentes, detectar automáticamente mediante palabras clave y semántica
        if (empty($categoryIds)) {
            $categoryIds = $this->detectCategories($game);
        }

        if (!empty($categoryIds)) {
            $game->categories()->sync($categoryIds);
        }

        return Category::whereIn('id', $categoryIds)->pluck('name')->toArray();
    }

    /**
     * Detecta las categorías idóneas analizando título, descripción y consola
     *
     * @param Game $game
     * @return array
     */
    public function detectCategories(Game $game): array
    {
        $text = mb_strtolower($game->title . ' ' . ($game->description ?? ''));
        $matchedSlugs = [];

        // Diccionario semántico de géneros para ROMs clásicas
        $keywords = [
            'carreras' => ['racing', 'drift', 'kart', 'drive', 'driving', 'speed', 'gran turismo', 'forza', 'need for speed', 'f1', 'formula 1', 'nascar', 'rally', 'moto', 'motogp', 'velocity', 'cruis', 'burnout', 'ridge racer', 'wipeout', 'outrun', 'carrera', 'conduccion', 'conducción', 'asphalt', 'midnight club'],
            'rpg-jrpg' => ['rpg', 'role playing', 'final fantasy', 'dragon quest', 'pokemon', 'pokémon', 'persona', 'tales of', 'valkyrie', 'mana', 'chronicles', 'dungeon', 'fire emblem', 'zelda', 'xenogears', 'xenosaga', 'kingdom hearts', 'golden sun', 'suikoden', 'breath of fire', 'shin megami', 'chrono trigger', 'diablo', 'star ocean', 'disgaea', 'juego de rol'],
            'plataformas' => ['mario', 'sonic', 'crash bandicoot', 'spyro', 'rayman', 'donkey kong', 'kirby', 'platform', 'platformer', 'plataforma', 'jump', 'banjo', 'jak and daxter', 'ratchet', 'clank', 'sly cooper', 'gex', 'klonoa', 'mega man', 'rockman', 'castlevania', 'metroid', 'wario', 'yoshi'],
            'lucha' => ['fighter', 'fighting', 'tekken', 'street fighter', 'mortal kombat', 'smash bros', 'kof', 'king of fighters', 'guilty gear', 'brawl', 'versus', 'fatal fury', 'samurai shodown', 'virtua fighter', 'dead or alive', 'soulcalibur', 'soul calibur', 'dragon ball z', 'budokai', 'naruto', 'lucha'],
            'shooter-fps' => ['shooter', 'fps', 'tps', 'doom', 'halo', 'call of duty', 'medal of honor', 'quake', 'unreal', 'gradius', 'r-type', 'contra', 'metal slug', 'shmup', 'shoot', 'goldeneye', 'perfect dark', 'time crisis', 'house of the dead', 'star fox', 'panzer dragoon', 'ikaruga', 'raiden', 'disparo'],
            'deportes' => ['fifa', 'pes', 'pro evolution', 'nba', 'nfl', 'nhl', 'madden', 'tennis', 'golf', 'skate', 'tony hawk', 'wwe', 'wrestling', 'deporte', 'soccer', 'futbol', 'fútbol', 'football', 'baseball', 'beisbol', 'olympic', 'boxing', 'boxeo'],
            'terror-survival' => ['resident evil', 'silent hill', 'alone in the dark', 'dead space', 'fatal frame', 'project zero', 'parasite eve', 'outlast', 'zombie', 'horror', 'survival', 'terror', 'evil', 'fear', 'clock tower', 'dino crisis', 'siren'],
            'estrategia' => ['strategy', 'tactics', 'tactical', 'advance wars', 'civilization', 'command & conquer', 'age of empires', 'warcraft', 'starcraft', 'chess', 'estrategia', 'tactico', 'táctico', 'ogre battle', 'tactics ogre', 'front mission', 'simcity', 'tycoon'],
            'puzzle' => ['puzzle', 'tetris', 'puyo', 'dr mario', 'snake', 'columns', 'bust-a-move', 'puzzle bobble', 'lemmings', 'brain', 'lumines', 'boulder dash', 'bomberman', 'qix', 'pac-man', 'pacman'],
        ];

        foreach ($keywords as $slug => $terms) {
            foreach ($terms as $term) {
                if (preg_match('/\b' . preg_quote($term, '/') . '\b/iu', $text) || str_contains($text, $term)) {
                    $matchedSlugs[] = $slug;
                    break;
                }
            }
        }

        $categoryIds = [];
        if (!empty($matchedSlugs)) {
            $categoryIds = Category::whereIn('slug', array_unique($matchedSlugs))->pluck('id')->toArray();
        }

        // Fallback garantizado: si aún no tiene ninguna categoría, asignar "Acción & Aventura" o la primera de la BD
        if (empty($categoryIds)) {
            $defaultCat = Category::where('slug', 'accion-aventura')->first() ?? Category::first();
            if ($defaultCat) {
                $categoryIds[] = $defaultCat->id;
            }
        }

        return array_values(array_unique($categoryIds));
    }
}
