<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'console_id',
        'cover_url',
        'cover_thumb_url',
        'banner_url',
        'description',
        'release_year',
        'developer',
        'publisher',
        'serial',
        'region',
        'languages',
        'file_size',
        'file_size_bytes',
        'file_format',
        'download_url',
        'mirror_url',
        'download_links',
        'crc32',
        'sha256',
        'meta_title',
        'meta_description',
        'download_count',
        'views_count',
        'rating_average',
        'rating_count',
        'status',
        'is_spotlight',
        'is_featured',
    ];

    protected $casts = [
        'release_year' => 'integer',
        'file_size_bytes' => 'integer',
        'download_links' => 'array',
        'download_count' => 'integer',
        'views_count' => 'integer',
        'rating_average' => 'float',
        'rating_count' => 'integer',
        'is_spotlight' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        if (is_numeric($value)) {
            $game = $this->where('id', $value)->first();
            if ($game) {
                return $game;
            }
        }

        return $this->where('slug', $value)->firstOrFail();
    }

    public function console(): BelongsTo
    {
        return $this->belongsTo(Console::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'game_category');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'game_badge');
    }

    public function franchises(): BelongsToMany
    {
        return $this->belongsToMany(Franchise::class, 'franchise_game');
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class)->orderBy('order', 'asc');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->latest();
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'game_id', 'user_id')->withTimestamps();
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!empty($this->file_size)) {
            return $this->file_size;
        }

        $bytes = (int) $this->file_size_bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }

        return $bytes > 0 ? $bytes . ' B' : '1.2 GB';
    }

    /**
     * Retorna lista unificada de servidores de descarga
     */
    public function getAllDownloadLinksAttribute(): array
    {
        $links = [];

        if (!empty($this->download_url)) {
            $links[] = [
                'server' => 'Servidor Principal (Cloudflare R2 Directo)',
                'url' => $this->download_url,
                'type' => 'r2',
                'badge' => 'Ultra Rápido',
                'color' => 'blue',
            ];
        }

        if (!empty($this->mirror_url)) {
            $links[] = [
                'server' => 'Servidor Espejo (Mirror 1)',
                'url' => $this->mirror_url,
                'type' => 'mirror',
                'badge' => 'Alternativo',
                'color' => 'emerald',
            ];
        }

        if (!empty($this->download_links) && is_array($this->download_links)) {
            foreach ($this->download_links as $link) {
                if (!empty($link['url'])) {
                    $serverName = !empty($link['server']) ? $link['server'] : 'Servidor Alternativo';
                    $type = 'custom';
                    $badge = 'Mirror';
                    $color = 'purple';

                    if (stripos($serverName, 'mega') !== false) {
                        $type = 'mega';
                        $badge = 'Nube Mega';
                        $color = 'rose';
                    } elseif (stripos($serverName, 'mediafire') !== false) {
                        $type = 'mediafire';
                        $badge = 'MediaFire';
                        $color = 'cyan';
                    } elseif (stripos($serverName, 'drive') !== false || stripos($serverName, 'google') !== false) {
                        $type = 'gdrive';
                        $badge = 'Google Drive';
                        $color = 'amber';
                    } elseif (stripos($serverName, '1fichier') !== false) {
                        $type = '1fichier';
                        $badge = '1Fichier';
                        $color = 'orange';
                    } elseif (stripos($serverName, 'torrent') !== false || stripos($serverName, 'magnet') !== false) {
                        $type = 'torrent';
                        $badge = 'P2P Torrent';
                        $color = 'emerald';
                    }

                    $links[] = [
                        'server' => $serverName,
                        'url' => $link['url'],
                        'type' => $type,
                        'badge' => $badge,
                        'color' => $color,
                    ];
                }
            }
        }

        return $links;
    }
}
