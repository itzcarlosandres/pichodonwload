<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedInteger('downloads')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->unsignedBigInteger('bandwidth_bytes')->default(0);
            $table->timestamps();
        });

        // Inicializar el día actual con las descargas y visitas acumuladas previas
        try {
            $today = now()->toDateString();
            $initialDownloads = \Illuminate\Support\Facades\DB::table('games')->sum('download_count') ?? 0;
            $initialViews = \Illuminate\Support\Facades\DB::table('games')->sum('views_count') ?? 0;

            \Illuminate\Support\Facades\DB::table('daily_stats')->insertOrIgnore([
                'date' => $today,
                'downloads' => (int) $initialDownloads,
                'views' => (int) $initialViews,
                'bandwidth_bytes' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // Ignorar en caso de entorno sin datos iniciales
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stats');
    }
};
