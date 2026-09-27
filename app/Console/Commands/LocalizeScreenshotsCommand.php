<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Screenshot;
use App\Services\ImageOptimizationService;

class LocalizeScreenshotsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roms:localize-screenshots {--force : Forzar reprocesamiento de todas las capturas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descarga todas las capturas externas (ej. CDRomance) al servidor local y las convierte a WebP';

    /**
     * Execute the console command.
     */
    public function handle(ImageOptimizationService $imageService): int
    {
        $this->info("Buscando capturas con enlaces remotos/externos...");

        $query = Screenshot::query();
        if (!$this->option('force')) {
            $query->where('image_url', 'LIKE', '%cdromance%')
                  ->orWhere('image_url', 'LIKE', 'http%')
                  ->where('image_url', 'NOT LIKE', '%/uploads/%');
        }

        $screenshots = $query->get();

        if ($screenshots->isEmpty()) {
            $this->info("¡Excelente! No hay capturas externas pendientes. Todas las imágenes están alojadas localmente.");
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$screenshots->count()} capturas para procesar.");
        $bar = $this->output->createProgressBar($screenshots->count());
        $bar->start();

        $successCount = 0;
        $failedCount = 0;

        foreach ($screenshots as $screenshot) {
            $remoteUrl = $screenshot->image_url;
            
            // Si ya es local, saltar
            if (str_contains($remoteUrl, '/uploads/screenshots/')) {
                $bar->advance();
                continue;
            }

            $localUrl = $imageService->downloadAndProcessScreenshot($remoteUrl);

            if ($localUrl) {
                $screenshot->update([
                    'image_url' => $localUrl,
                    'image_webp_url' => $localUrl,
                ]);
                $successCount++;
            } else {
                $failedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Proceso completado: {$successCount} capturas descargadas y convertidas a WebP local.");
        if ($failedCount > 0) {
            $this->warn("{$failedCount} capturas no pudieron descargarse debido a enlaces rotos o inaccesibles.");
        }

        return Command::SUCCESS;
    }
}
