<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Franchise extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subtitle',
        'description',
        'image',
        'icon',
        'color',
        'search_terms',
        'is_active',
        'order',
    ];

    protected $casts = [
        'search_terms' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'franchise_game')->withPivot('order')->orderByPivot('order');
    }
}
