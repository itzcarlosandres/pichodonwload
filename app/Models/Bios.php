<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bios extends Model
{
    protected $table = 'bios';

    protected $fillable = [
        'system',
        'console_slug',
        'console_id',
        'files',
        'version',
        'size',
        'format',
        'md5',
        'sha1',
        'emulator',
        'download_url',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function console(): BelongsTo
    {
        return $this->belongsTo(Console::class);
    }
}
