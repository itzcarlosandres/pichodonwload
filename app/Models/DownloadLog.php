<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    protected $fillable = [
        'downloadable_type',
        'downloadable_id',
        'item_title',
        'file_size_bytes',
        'ip_hash',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];
}
