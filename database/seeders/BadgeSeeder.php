<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Verificado 60 FPS',
                'slug' => 'verificado-60-fps',
                'text_color' => '#34d399',
                'bg_color' => '#064e3b',
                'border_color' => '#059669',
                'icon' => 'zap',
                'description' => 'Rendimiento fluido probado a 60 cuadros por segundo en emuladores recomendados.',
            ],
            [
                'name' => 'No-Intro Verified',
                'slug' => 'no-intro-verified',
                'text_color' => '#60a5fa',
                'bg_color' => '#1e3a8a',
                'border_color' => '#2563eb',
                'icon' => 'check-circle',
                'description' => 'Volcado 1:1 verificado contra la base de datos oficial de preservación No-Intro.',
            ],
            [
                'name' => 'Redump 1:1 Clean',
                'slug' => 'redump-clean',
                'text_color' => '#c084fc',
                'bg_color' => '#3b0764',
                'border_color' => '#7c3aed',
                'icon' => 'disc',
                'description' => 'Copia binaria exacta sin modificaciones del disco óptico original.',
            ],
            [
                'name' => 'Traducción Español',
                'slug' => 'traduccion-espanol',
                'text_color' => '#fbbf24',
                'bg_color' => '#78350f',
                'border_color' => '#d97706',
                'icon' => 'globe',
                'description' => 'Incluye parche de traducción al castellano realizado por la comunidad.',
            ],
            [
                'name' => 'HD Texture Pack',
                'slug' => 'hd-texture-pack',
                'text_color' => '#38bdf8',
                'bg_color' => '#083344',
                'border_color' => '#0891b2',
                'icon' => 'sparkles',
                'description' => 'Compatible con paquetes de texturas en alta definición y reescalado 4K.',
            ],
            [
                'name' => 'Juego del Mes',
                'slug' => 'juego-del-mes',
                'text_color' => '#fb7185',
                'bg_color' => '#881337',
                'border_color' => '#e11d48',
                'icon' => 'flame',
                'description' => 'Título destacado por votación de la comunidad.',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge);
        }
    }
}
