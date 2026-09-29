<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DailyStat extends Model
{
    protected $fillable = [
        'date',
        'downloads',
        'views',
        'bandwidth_bytes',
    ];

    protected $casts = [
        'date' => 'date',
        'downloads' => 'integer',
        'views' => 'integer',
        'bandwidth_bytes' => 'integer',
    ];

    /**
     * Registra una descarga tanto a nivel agregado como en log individual
     */
    public static function recordDownload(
        int $bytes = 0,
        string $type = 'game',
        ?int $downloadableId = null,
        ?string $title = null,
        ?string $ip = null
    ): void {
        $today = now()->toDateString();

        try {
            // Actualizar o crear registro diario
            $stat = static::firstOrCreate(
                ['date' => $today],
                ['downloads' => 0, 'views' => 0, 'bandwidth_bytes' => 0]
            );

            $stat->increment('downloads');
            if ($bytes > 0) {
                $stat->increment('bandwidth_bytes', $bytes);
            }

            // Registrar en log individual para trazabilidad
            DownloadLog::create([
                'downloadable_type' => $type,
                'downloadable_id' => $downloadableId,
                'item_title' => $title,
                'file_size_bytes' => max(0, $bytes),
                'ip_hash' => $ip ? hash('sha256', $ip . config('app.key')) : null,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Registra una visita en el agregador diario
     */
    public static function recordView(): void
    {
        $today = now()->toDateString();

        try {
            $stat = static::firstOrCreate(
                ['date' => $today],
                ['downloads' => 0, 'views' => 0, 'bandwidth_bytes' => 0]
            );

            $stat->increment('views');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Retorna la tendencia de los últimos 7 días estructurada para Chart.js
     *
     * @return array{labels: array<int, string>, downloads: array<int, int>, views: array<int, int>, bandwidth_mb: array<int, float>}
     */
    public static function getWeeklyTrend(): array
    {
        $days = 7;
        $labels = [];
        $downloads = [];
        $views = [];
        $bandwidth = [];

        $startDate = now()->subDays($days - 1)->toDateString();
        $records = static::where('date', '>=', $startDate)
            ->get()
            ->keyBy(function (DailyStat $stat): string {
                return Carbon::parse($stat->date)->format('Y-m-d');
            });

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->toDateString();

            if ($i === 0) {
                $labels[] = 'Hoy';
            } elseif ($i === 1) {
                $labels[] = 'Ayer';
            } else {
                $labels[] = $date->locale('es')->isoFormat('D MMM');
            }

            /** @var DailyStat|null $record */
            $record = $records->get($dateKey);
            $downloads[] = $record ? (int) $record->downloads : 0;
            $views[] = $record ? (int) $record->views : 0;
            $bandwidth[] = $record ? round($record->bandwidth_bytes / 1048576, 2) : 0.0;
        }

        return [
            'labels' => $labels,
            'downloads' => $downloads,
            'views' => $views,
            'bandwidth_mb' => $bandwidth,
        ];
    }
}
