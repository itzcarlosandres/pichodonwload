<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\AiContentService;
use Illuminate\Console\Command;

class EnrichGamesAiCommand extends Command
{
    protected $signature = 'games:enrich-ai
                            {--limit=25 : Cantidad máxima de juegos a procesar}
                            {--all : Procesar todos los juegos que tengan contenido duplicado}
                            {--force : Forzar reescritura incluso si no tienen la plantilla fija}';

    protected $description = 'Reescribe y enriquece juegos existentes con Inteligencia Artificial (Groq / Gemini) para eliminar contenido duplicado';

    public function handle(AiContentService $aiService): int
    {
        $this->info('Iniciando optimización de contenido SEO con IA...');

        $query = Game::with('console');

        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->where('description', 'LIKE', '%Ambientado en un universo fascinante%')
                    ->orWhere('meta_description', 'LIKE', '%con volcado verificado No-Intro/Redump%')
                    ->orWhereNull('description')
                    ->orWhere('description', '');
            });
        }

        $totalFound = $query->count();

        if ($totalFound === 0) {
            $this->info('¡Excelente! No hay juegos con contenido duplicado o pendiente de enriquecer.');

            return self::SUCCESS;
        }

        $this->warn("Se encontraron {$totalFound} juegos con plantilla repetida o pendiente.");

        $limit = $this->option('all') ? $totalFound : (int) $this->option('limit');
        $games = $query->limit($limit)->get();

        $bar = $this->output->createProgressBar($games->count());
        $bar->start();

        $updatedCount = 0;

        foreach ($games as $game) {
            $consoleName = $game->console?->name ?? 'Retro Console';

            $context = [
                'release_year' => $game->release_year,
                'region' => $game->region,
                'languages' => $game->languages,
                'genre' => $game->genre,
                'publisher' => $game->publisher,
                'developer' => $game->developer,
            ];

            try {
                // Generar artículo y sinopsis profunda
                $rich = $aiService->generateRichDescription($game->title, $consoleName, $context);
                if (! empty($rich['description'])) {
                    $game->description = $rich['description'];
                }

                // Generar metatags SEO únicos
                $seo = $aiService->generateSeo($game->title, $consoleName, $context);
                if (! empty($seo['meta_title'])) {
                    $game->meta_title = $seo['meta_title'];
                }
                if (! empty($seo['meta_description'])) {
                    $game->meta_description = $seo['meta_description'];
                }

                $game->save();
                $updatedCount++;
            } catch (\Throwable $e) {
                // Continuar con el siguiente sin detener el lote
            }

            $bar->advance();
            // Pequeña pausa de 200ms para respetar cualquier límite de peticiones
            usleep(200000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("¡Éxito! Se actualizaron {$updatedCount} juegos con contenido 100% único.");

        return self::SUCCESS;
    }
}
