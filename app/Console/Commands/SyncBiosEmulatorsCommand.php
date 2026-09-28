<?php

namespace App\Console\Commands;

use Database\Seeders\BiosEmulatorSeeder;
use Illuminate\Console\Command;
use App\Models\Bios;
use App\Models\Emulator;

class SyncBiosEmulatorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-bios-emulators {--force : Sobrescribir datos sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza y actualiza la base de datos de BIOS (38 packs de CDRomance) y el directorio de Emuladores';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Iniciando sincronización de BIOS (CDRomance) y Emuladores Oficiales...');

        $seeder = new BiosEmulatorSeeder();
        $seeder->run();

        $totalBios = Bios::where('is_active', true)->count();
        $totalEmulators = Emulator::where('is_active', true)->count();

        $this->newLine();
        $this->table(
            ['Módulo', 'Total Registros Activos', 'Estado'],
            [
                ['Archivos BIOS', $totalBios, '✅ Sincronizado (38 Packs + PS3)'],
                ['Directorio de Emuladores', $totalEmulators, '✅ Sincronizado (19 Oficiales)'],
            ]
        );

        $this->info('✨ ¡Sincronización completada con éxito!');
        return Command::SUCCESS;
    }
}
