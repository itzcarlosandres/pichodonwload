<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Services\AiContentService;
use Illuminate\Support\Facades\Log;

class PublishDripGamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:publish-drip 
                            {--count= : Cantidad de juegos en cola a publicar} 
                            {--with-ai : Enriquecer con IA la descripción y SEO antes de publicar si están pendientes} 
                            {--force : Publicar incluso si el piloto automático está pausado en configuración} 
                            {--dry-run : Simular la publicación sin modificar la base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publica un lote dosificado de juegos en estado DRAFT (Drip-Feed SEO) para evitar penalizaciones de Google';

    /**
     * Execute the console command.
     */
    public function handle(
        AiContentService $aiService,
        \App\Services\CategorySyncService $categoryService
    ): int {
        $autopilot = \App\Models\Setting::get('roms_autopilot_enabled', config('roms.autopilot_enabled', true));
        if (!$autopilot && !$this->option('force')) {
            $this->warn('⏸️ El piloto automático está pausado en configuración (roms_autopilot_enabled=false). Usa --force para forzar la ejecución manual.');
            return self::SUCCESS;
        }

        $count = (int) ($this->option('count') ?: \App\Models\Setting::get('roms_posts_per_batch', config('roms.posts_per_batch', 4)));
        $dryRun = (bool) $this->option('dry-run');
        $withAi = (bool) $this->option('with-ai') || \App\Models\Setting::get('roms_auto_ai_enrich', config('roms.auto_ai_enrich', true));

        $this->info("🔍 Buscando juegos en cola (DRAFT) para publicar (Lote: {$count} juegos)...");

        // Obtenemos los borradores rotando equitativamente por consola para garantizar variedad
        $distinctConsoles = Game::where('status', 'DRAFT')
            ->select('console_id')
            ->distinct()
            ->inRandomOrder()
            ->pluck('console_id');

        $drafts = collect();
        if ($distinctConsoles->isNotEmpty()) {
            // Seleccionar 1 juego por consola en cada ronda hasta completar el cupo del lote
            while ($drafts->count() < $count && $distinctConsoles->isNotEmpty()) {
                $addedInRound = 0;
                foreach ($distinctConsoles as $cId) {
                    if ($drafts->count() >= $count) {
                        break;
                    }
                    $game = Game::with('console')
                        ->where('status', 'DRAFT')
                        ->where('console_id', $cId)
                        ->whereNotIn('id', $drafts->pluck('id'))
                        ->orderBy('created_at', 'asc')
                        ->first();
                    if ($game) {
                        $drafts->push($game);
                        $addedInRound++;
                    }
                }
                if ($addedInRound === 0) {
                    break;
                }
            }
        }

        // Si aún faltan para completar la tanda, rellenar con los borradores restantes
        if ($drafts->count() < $count) {
            $remaining = Game::with('console')
                ->where('status', 'DRAFT')
                ->whereNotIn('id', $drafts->pluck('id'))
                ->orderBy('created_at', 'asc')
                ->limit($count - $drafts->count())
                ->get();
            $drafts = $drafts->concat($remaining);
        }

        if ($drafts->isEmpty()) {
            $this->warn('ℹ️ No hay juegos en estado DRAFT pendientes en la cola.');
            Log::channel('single')->info('[Drip-Publisher] No hay juegos pendientes en DRAFT para publicar.');
            return self::SUCCESS;
        }

        $this->info("🎯 Se encontraron {$drafts->count()} juegos listos para procesar.");

        $publishedCount = 0;
        $publishedTitles = [];

        foreach ($drafts as $game) {
            $consoleName = $game->console ? $game->console->name : 'Retro Console';

            $this->line("⏳ Procesando: <info>{$game->title}</info> ({$consoleName})");

            // Si se solicita enriquecer con IA y la descripción es vacía o provisional
            $needsAi = empty($game->description) || str_contains($game->description, 'Pendiente de generar');
            if ($withAi && $needsAi && !$dryRun) {
                try {
                    $context = [
                        'release_year' => $game->release_year,
                        'region' => $game->region,
                        'languages' => $game->languages,
                        'publisher' => $game->publisher,
                        'developer' => $game->developer,
                    ];
                    $rich = $aiService->generateRichDescription($game->title, $consoleName, $context);
                    if (!empty($rich['description'])) {
                        $game->description = $rich['description'];
                    }

                    if (empty($game->meta_title) || empty($game->meta_description)) {
                        $seo = $aiService->generateSeo($game->title, $consoleName, $context);
                        if (!empty($seo['meta_title'])) $game->meta_title = $seo['meta_title'];
                        if (!empty($seo['meta_description'])) $game->meta_description = $seo['meta_description'];
                    }
                } catch (\Throwable $e) {
                    $this->error("⚠️ Error generando IA para {$game->title}: " . $e->getMessage());
                }
            }

            if ($dryRun) {
                $this->comment("   [DRY-RUN] Se habría publicado: {$game->title} (ID #{$game->id})");
            } else {
                // Garantizar que el juego tenga al menos una categoría asignada
                if ($game->categories()->count() === 0) {
                    $assignedCats = $categoryService->syncGame($game);
                    if (!empty($assignedCats)) {
                        $this->line("   🏷️ <fg=cyan>[CATEGORÍAS AUTO-ASIGNADAS]</> " . implode(', ', $assignedCats));
                    }
                }

                $game->status = 'PUBLISHED';
                $game->updated_at = now();
                $game->save();

                $publishedCount++;
                $publishedTitles[] = "{$game->title} [{$consoleName}]";
                $this->info("   ✅ Publicado exitosamente: {$game->title}");
            }
        }

        // Registrar en el log de goteo
        $remainingDrafts = Game::where('status', 'DRAFT')->count();

        $logMessage = sprintf(
            "[Drip-Publisher] %s - Publicados: %d juegos (%s). Restantes en cola DRAFT: %d",
            $dryRun ? '[SIMULACIÓN]' : '[REAL]',
            $publishedCount,
            implode(', ', $publishedTitles),
            $remainingDrafts
        );

        $logPath = storage_path('logs/drip-publisher.log');
        @file_put_contents($logPath, '[' . now()->toDateTimeString() . '] ' . $logMessage . PHP_EOL, FILE_APPEND);

        $this->newLine();
        $this->info("🚀 Resumen del Goteo SEO:");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Juegos publicados en esta tanda', $publishedCount],
                ['Modo', $dryRun ? 'Simulación (Dry-Run)' : 'En Vivo (Publicado)'],
                ['Juegos restantes en cola (DRAFT)', $remainingDrafts],
                ['Próxima ejecución estimada', 'En ' . config('roms.batch_interval_hours', 2) . ' horas'],
            ]
        );

        // Alerta si la cola se está quedando vacía
        if ($remainingDrafts < 5) {
            $this->warn("⚠️ Atención: La cola tiene solo {$remainingDrafts} juegos en reserva. Considera cosechar más con `php artisan roms:auto-harvest`.");
        }

        return self::SUCCESS;
    }
}
