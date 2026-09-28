<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Franchise;
use App\Services\FranchiseSyncService;

class SyncFranchisesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'franchises:sync 
                            {--franchise= : Slug o nombre de una franquicia específica} 
                            {--dry-run : Simular la vinculación sin modificar la base de datos} 
                            {--force : Sobrescribir vínculos previos con las coincidencias automáticas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza y vincula automáticamente todos los juegos del catálogo con sus respectivas Sagas / Franquicias';

    /**
     * Execute the console command.
     */
    public function handle(FranchiseSyncService $syncService): int
    {
        $franchiseOption = $this->option('franchise');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("⚡ Iniciando sincronización inteligente de Sagas y Franquicias..." . ($dryRun ? " [MODO SIMULACIÓN]" : ""));

        $franchiseId = null;
        if ($franchiseOption) {
            $franchise = Franchise::where('slug', $franchiseOption)
                ->orWhere('name', 'LIKE', "%{$franchiseOption}%")
                ->first();

            if (!$franchise) {
                $this->error("❌ No se encontró ninguna saga con el término '{$franchiseOption}'.");
                return self::FAILURE;
            }

            $franchiseId = $franchise->id;
            $this->line("🎯 Filtrando exclusivamente por la saga: <fg=cyan>{$franchise->name}</>");
        }

        $results = $syncService->syncAllFranchises($franchiseId, $dryRun);

        $this->newLine();
        $this->line("════════════════════════════════════════════════════════════════");
        $this->line("<fg=yellow;options=bold>📊 RESUMEN DE SINCRONIZACIÓN DE SAGAS</>");
        $this->line("════════════════════════════════════════════════════════════════");

        $rows = [];
        foreach ($results['franchise_details'] as $name => $det) {
            $diff = $det['matched_count'] - $det['previous_count'];
            $diffText = $diff > 0 ? "<fg=green>+{$diff} nuevos</>" : "<fg=gray>Sin cambios</>";

            $rows[] = [
                $name,
                $det['previous_count'],
                "<fg=cyan>{$det['matched_count']}</>",
                $diffText,
            ];
        }

        $this->table(
            ['Saga / Franquicia', 'Juegos Previos', 'Juegos Coincidentes', 'Novedades'],
            $rows
        );

        $this->newLine();
        $this->info("🎮 Total de juegos escaneados: {$results['total_games_scanned']}");
        $this->info("✨ Sagas procesadas: {$results['total_franchises']}");
        $this->info("🔗 Nuevas vinculaciones establecidas: {$results['links_created']}");

        if ($dryRun) {
            $this->comment("⚠️ Ningún dato fue modificado porque se utilizó la opción --dry-run.");
        } else {
            $this->info("🎉 ¡Catálogo y colecciones sincronizados con éxito!");
        }

        return self::SUCCESS;
    }
}
