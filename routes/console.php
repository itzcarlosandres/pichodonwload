<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tareas Programadas Automáticas (Laravel Scheduler)
|--------------------------------------------------------------------------
|
| Configuración para el cron maestro de aaPanel / Linux:
| * * * * * cd /www/wwwroot/pichodonwload && php artisan schedule:run >> /dev/null 2>&1
|
*/

// 1. PUBLICACIÓN DOSIFICADA (DRIP PUBLISHING): Cada 2 horas publica 4 juegos solos
Schedule::command('games:publish-drip')
    ->everyTwoHours()
    ->withoutOverlapping(15)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/drip-publisher.log'));

// 2. AUTO-COSECHA DE JUEGOS: Explora CDRomance y Romspedia a diario para rellenar la cola
Schedule::command('roms:auto-harvest')
    ->dailyAt('02:00')
    ->withoutOverlapping(30)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/auto-harvest.log'));

// 3. LIMPIEZA DE TOKENS Y SESIONES EXPIRADAS (Diario a las 03:00 AM)
Schedule::command('auth:clear-resets')
    ->dailyAt('03:00')
    ->withoutOverlapping();

// 4. MANTENIMIENTO, DESFRAGMENTACIÓN DE BD Y ROTACIÓN DE LOGS (Semanal domingos 04:00 AM)
Schedule::command('app:maintenance-optimize')
    ->weeklyOn(0, '04:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/maintenance.log'));

