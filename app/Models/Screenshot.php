<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screenshot extends Model
{
    use HasFactory;

    protected $table = 'game_screenshots';

    protected $fillable = [
        'game_id',
        'image_url',
        'image_webp_url',
        'order',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
