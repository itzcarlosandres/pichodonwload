<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'user_id',
        'author_name',
        'score',
        'comment',
        'tested_hardware',
        'tested_emulator',
        'fps_performance',
        'is_approved',
    ];

    protected $casts = [
        'score' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
