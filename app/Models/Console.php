<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Console extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'manufacturer',
        'generation',
        'release_year',
        'logo_url',
        'banner_url',
        'description',
        'recommended_emulator',
        'order',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'generation' => 'integer',
        'release_year' => 'integer',
        'order' => 'integer',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
