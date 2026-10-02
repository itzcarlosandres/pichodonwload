<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'console_id',
        'title',
        'region',
        'notes',
        'votes_count',
        'status',
        'completed_game_id',
        'requester_name',
        'ip_hash',
    ];

    protected $casts = [
        'votes_count' => 'integer',
        'console_id' => 'integer',
        'user_id' => 'integer',
        'completed_game_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function console(): BelongsTo
    {
        return $this->belongsTo(Console::class);
    }

    public function completedGame(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'completed_game_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(GameRequestVote::class, 'game_request_id');
    }

    /**
     * Check if the current user or IP hash has already voted on this request.
     */
    public function hasVoted(string $ipHash, ?int $userId = null): bool
    {
        return $this->votes()
            ->where(function ($query) use ($ipHash, $userId) {
                $query->where('ip_hash', $ipHash);
                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->exists();
    }

    /**
     * Human-friendly status metadata.
     */
    public function getStatusMeta(): array
    {
        return match ($this->status) {
            'completed' => [
                'label' => '¡Completado / Disponible!',
                'color' => '#10B981',
                'bg' => '#ECFDF5',
                'border' => '#A7F3D0',
                'icon' => 'check-circle-2',
            ],
            'in_progress' => [
                'label' => 'Buscando ROM / En Subida',
                'color' => '#3B82F6',
                'bg' => '#EFF6FF',
                'border' => '#BFDBFE',
                'icon' => 'loader',
            ],
            'rejected' => [
                'label' => 'No disponible / Rechazado',
                'color' => '#EF4444',
                'bg' => '#FEF2F2',
                'border' => '#FECACA',
                'icon' => 'x-circle',
            ],
            default => [
                'label' => 'Esperando Votos',
                'color' => '#F59E0B',
                'bg' => '#FFFBEB',
                'border' => '#FDE68A',
                'icon' => 'clock',
            ],
        };
    }
}
