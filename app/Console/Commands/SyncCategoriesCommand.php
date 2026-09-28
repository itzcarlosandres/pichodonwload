<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Services\CategorySyncService;

class SyncCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:sync-categories 
                            {--game= : ID específico de juego a categorizar} 
                            {--force : Forzar re-evaluación en todos los juegos, incluso si ya tienen categorías asignadas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza y asigna automáticamente géneros y categorías a los juegos que no tienen ninguna seleccionada';

    /**
     * Execute the console command.
     */
    public function handle(CategorySyncService $categoryService): int
    {
        $gameId = $this->option('game');
        $force = (bool) $this->option('force');

        $this->info("🔍 Buscando juegos que requieren asignación automática de categoría...");

        $query = Game::with('categories');

        if ($gameId) {
            $query->where('id', $gameId);
        } elseif (!$force) {
            // Solo los que tienen 0 categorías asociadas
            $query->doesntHave('categories');
        }

        $games = $query->orderBy('id', 'asc')->get();

        if ($games->isEmpty()) {
            $this->info("✨ ¡Excelente! Todos los juegos ya tienen sus categorías y géneros asignados.");
            return self::SUCCESS;
        }

        $this->info("🎯 Se encontraron {$games->count()} juegos para categorizar.");
        $this->newLine();

        $syncedCount = 0;

        foreach ($games as $index => $game) {
            $num = $index + 1;
            $assignedNames = $categoryService->syncGame($game, $force);

            $catString = !empty($assignedNames) ? implode(', ', $assignedNames) : 'Acción & Aventura';
            $this->line("<fg=cyan>[{$num}/{$games->count()}]</> 🎮 <comment>#{$game->id} {$game->title}</comment> -> <fg=green>[{$catString}]</>");
            $syncedCount++;
        }

        $this->newLine();
        $this->info("══════════════════════════════════════════════");
        $this->info("🎉 Categorización completada con éxito.");
        $this->info("   ✅ Juegos actualizados: {$syncedCount}");
        $this->info("══════════════════════════════════════════════");

        return self::SUCCESS;
    }
}
