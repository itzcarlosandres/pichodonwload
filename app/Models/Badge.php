<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'text_color',
        'bg_color',
        'border_color',
        'icon',
        'description',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_badge');
    }

    public function getBgColorAttribute($value): string
    {
        if (empty($value) || str_starts_with($value, 'bg-')) {
            if (str_contains($value ?? '', 'emerald')) return '#064e3b';
            if (str_contains($value ?? '', 'blue')) return '#1e3a8a';
            if (str_contains($value ?? '', 'purple')) return '#3b0764';
            if (str_contains($value ?? '', 'amber')) return '#78350f';
            if (str_contains($value ?? '', 'cyan')) return '#083344';
            if (str_contains($value ?? '', 'rose')) return '#881337';
            return '#11141A';
        }
        return $value;
    }

    public function getTextColorAttribute($value): string
    {
        if (empty($value) || str_starts_with($value, 'text-')) {
            if (str_contains($value ?? '', 'emerald')) return '#34d399';
            if (str_contains($value ?? '', 'blue')) return '#60a5fa';
            if (str_contains($value ?? '', 'purple')) return '#c084fc';
            if (str_contains($value ?? '', 'amber')) return '#fbbf24';
            if (str_contains($value ?? '', 'cyan')) return '#38bdf8';
            if (str_contains($value ?? '', 'rose')) return '#fb7185';
            return '#ffffff';
        }
        return $value;
    }

    public function getBorderColorAttribute($value): string
    {
        if (empty($value) || str_starts_with($value, 'border-')) {
            if (str_contains($value ?? '', 'emerald')) return '#059669';
            if (str_contains($value ?? '', 'blue')) return '#2563eb';
            if (str_contains($value ?? '', 'purple')) return '#7c3aed';
            if (str_contains($value ?? '', 'amber')) return '#d97706';
            if (str_contains($value ?? '', 'cyan')) return '#0891b2';
            if (str_contains($value ?? '', 'rose')) return '#e11d48';
            return '#232936';
        }
        return $value;
    }

    public function getIconAttribute($value): string
    {
        if ($value === 'check-circle-2') return 'check-circle';
        return $value ?: 'shield-check';
    }
}
