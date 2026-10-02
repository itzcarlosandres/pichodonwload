<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRequestVote extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'game_request_id',
        'user_id',
        'ip_hash',
        'created_at',
    ];

    protected $casts = [
        'game_request_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(GameRequest::class, 'game_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
