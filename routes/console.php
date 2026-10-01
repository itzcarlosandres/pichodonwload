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

// 1. PUBLICACIÓN DOSIFICADA (DRIP PUBLISHING): Programación dinámica controlada desde el panel admin
$dripCommand = Schedule::command('games:publish-drip');

$intervalHours = 2;
try {
    $intervalHours = (int) \App\Models\Setting::get('roms_batch_interval_hours', config('roms.batch_interval_hours', 2));
    $intervalHours = max(1, min(72, $intervalHours));
} catch (\Throwable $e) {
    $intervalHours = (int) config('roms.batch_interval_hours', 2);
}

match ($intervalHours) {
    1 => $dripCommand->hourly(),
    2 => $dripCommand->everyTwoHours(),
    3 => $dripCommand->everyThreeHours(),
    4 => $dripCommand->everyFourHours(),
    6 => $dripCommand->everySixHours(),
    12 => $dripCommand->twiceDaily(0, 12),
    24 => $dripCommand->dailyAt('00:00'),
    default => $dripCommand->cron("0 */{$intervalHours} * * *"),
};

$dripCommand
    ->when(function () {
        try {
            return (bool) \App\Models\Setting::get('roms_autopilot_enabled', config('roms.autopilot_enabled', true));
        } catch (\Throwable $e) {
            return true;
        }
    })
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

