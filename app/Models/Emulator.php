<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emulator extends Model
{
    protected $fillable = [
        'name',
        'system',
        'icon',
        'platforms',
        'version',
        'features',
        'license',
        'website',
        'download_url',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'platforms' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
}
