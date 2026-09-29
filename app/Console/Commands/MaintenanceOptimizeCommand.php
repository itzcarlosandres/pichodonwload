<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Support\Facades\Cache;

class MaintenanceOptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:maintenance-optimize 
                            {--days=14 : Días de retención para archivos de logs} 
                            {--skip-db : Omitir optimización de tablas de base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mantenimiento y optimización automática: depuración de logs viejos, optimización de BD y precalentamiento de caché';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🛠️  Iniciando rutina de optimización y mantenimiento de la plataforma...');

        // 1. Limpieza y rotación de logs antiguos
        $this->cleanOldLogs((int) $this->option('days'));

        // 2. Limpieza de tokens de contraseña vencidos
        $this->line('🔑 Limpiando tokens de reseteo expirados...');
        Artisan::call('auth:clear-resets');

        // 3. Optimización de tablas de base de datos
        if (!$this->option('skip-db')) {
            $this->optimizeDatabaseTables();
        }

        // 4. Saneamiento de descripciones con posibles fragmentos de enlaces corruptos
        $this->cleanCorruptedDescriptions();

        // 5. Precalentamiento de caché (Cache Warming)
        $this->warmHotCaches();

        $this->newLine();
        $this->info('✅ ¡Mantenimiento y optimización completados con éxito!');

        return self::SUCCESS;
    }

    /**
     * Depura archivos de logs antiguos para evitar saturación de disco
     */
    protected function cleanOldLogs(int $days): void
    {
        $this->line("🧹 Analizando archivos de registro en storage/logs (Retención: {$days} días)...");
        $logPath = storage_path('logs');
        $deletedCount = 0;
        $freedBytes = 0;

        if (File::isDirectory($logPath)) {
            $files = File::files($logPath);
            $cutoff = now()->subDays($days)->timestamp;

            foreach ($files as $file) {
                // Conservar archivo principal laravel.log pero truncar si supera los 30MB
                if ($file->getFilename() === 'laravel.log') {
                    if ($file->getSize() > 30 * 1024 * 1024) {
                        $freedBytes += $file->getSize();
                        File::put($file->getRealPath(), '');
                        $this->line("   ✂️ laravel.log superaba 30MB; se ha rotado.");
                    }
                    continue;
                }

                if ($file->getMTime() < $cutoff && $file->getExtension() === 'log') {
                    $freedBytes += $file->getSize();
                    File::delete($file->getRealPath());
                    $deletedCount++;
                }
            }
        }

        $mbFreed = round($freedBytes / (1024 * 1024), 2);
        $this->info("   🗑️ Logs eliminados: {$deletedCount} archivos ({$mbFreed} MB liberados)");
    }

    /**
     * Ejecuta OPTIMIZE TABLE sobre las tablas más activas en MySQL
     */
    protected function optimizeDatabaseTables(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') {
            $this->line("   ℹ️ El motor de BD es '{$driver}'. La desfragmentación de tablas solo aplica para MySQL/MariaDB.");
            return;
        }

        $this->line('🗄️  Desfragmentando y optimizando tablas principales de MySQL...');

        $tables = ['games', 'consoles', 'categories', 'category_game', 'reviews', 'user_favorites', 'sessions'];
        $optimized = 0;

        foreach ($tables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::statement("OPTIMIZE TABLE `{$table}`");
                    $optimized++;
                }
            } catch (\Throwable $e) {
                // Omitir si la tabla está bloqueada o en uso temporal
            }
        }

        $this->info("   ⚡ Tablas optimizadas correctamente: {$optimized}");
    }

    /**
     * Precarga en memoria/caché los datos más consultados del catálogo
     */
    protected function warmHotCaches(): void
    {
        $this->line('🔥 Precalentando caché para páginas de alto tráfico...');

        try {
            // Precargar consolas
            Cache::remember('consoles_active_all', 86400, function () {
                return Console::orderBy('name')->get();
            });

            // Precargar conteo de juegos publicados
            Cache::remember('stats_published_games_count', 3600, function () {
                return Game::where('status', 'PUBLISHED')->count();
            });

            $this->info('   ⚡ Caché precalentada con éxito.');
        } catch (\Throwable $e) {
            $this->warn('   ⚠️ No se pudo completar el precalentamiento de caché: ' . $e->getMessage());
        }
    }

    /**
     * Sanea descripciones con fragmentos de enlaces HTML o Markdown malformados
     */
    protected function cleanCorruptedDescriptions(): void
    {
        $this->line('🧹 Verificando integridad de descripciones de juegos...');

        try {
            $corrupted = Game::where('description', 'LIKE', '%class="text-[#CE2D2D]%')
                ->orWhere('description', 'LIKE', '%title="Ver catálogo completo%')
                ->orWhere('description', 'LIKE', '%href=%')
                ->orWhere('description', 'LIKE', '%<a%')
                ->get(['id', 'title', 'description']);

            $fixed = 0;
            foreach ($corrupted as $game) {
                $clean = $game->description;
                $clean = preg_replace('/\[[a-z0-9_-]+\]\([^)]*(?:%3Ca|<a\s+href)[^)]*\)"[^>]*>/iu', '', $clean);
                $clean = preg_replace('/<a\b[^>]*<a\b/iu', '<a', $clean);
                $clean = preg_replace('/class="text-\[#CE2D2D\][^"]*"[^>]*>/iu', '', $clean);

                if ($clean !== $game->description) {
                    $game->description = $clean;
                    $game->save();
                    $fixed++;
                }
            }

            if ($fixed > 0) {
                $this->info("   🛠️ Descripciones saneadas con éxito: {$fixed} juegos.");
            } else {
                $this->info("   ✨ Todas las descripciones se encuentran íntegras y limpias.");
            }
        } catch (\Throwable $e) {
            $this->warn('   ⚠️ No se pudo completar la verificación de descripciones: ' . $e->getMessage());
        }
    }
}
