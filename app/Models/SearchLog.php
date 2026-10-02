<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'query',
        'results_count',
        'ip_hash',
        'created_at',
    ];

    protected $casts = [
        'results_count' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Log a search query if valid.
     */
    public static function record(string $query, int $resultsCount, ?string $ip = null): ?self
    {
        $clean = trim(mb_substr($query, 0, 100));
        if (mb_strlen($clean) < 2) {
            return null;
        }

        $ipHash = $ip ? hash('sha256', $ip.'_vault_search_salt') : null;

        return self::create([
            'query' => strtolower($clean),
            'results_count' => $resultsCount,
            'ip_hash' => $ipHash,
            'created_at' => now(),
        ]);
    }
}
