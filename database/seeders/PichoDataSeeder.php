<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PichoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlPath = database_path('pichoroms_mysql_data.sql');

        if (!File::exists($sqlPath)) {
            $this->command->error("No se encontró el archivo: {$sqlPath}");
            return;
        }

        $this->command->info("Cargando datos de producción en la base de datos...");
        $sql = File::get($sqlPath);

        DB::unprepared($sql);

        $this->command->info("✅ ¡Todos los datos (juegos, consolas, categorías, configuraciones, usuarios) han sido importados con éxito!");
    }
}
