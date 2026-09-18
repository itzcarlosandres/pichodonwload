<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Acción & Aventura', 'slug' => 'accion-aventura', 'color' => '#3B82F6', 'icon' => 'sword'],
            ['name' => 'RPG & JRPG', 'slug' => 'rpg-jrpg', 'color' => '#8B5CF6', 'icon' => 'sparkles'],
            ['name' => 'Plataformas 3D & 2D', 'slug' => 'plataformas', 'color' => '#10B981', 'icon' => 'footprints'],
            ['name' => 'Lucha / Fighting', 'slug' => 'lucha', 'color' => '#EF4444', 'icon' => 'flame'],
            ['name' => 'Shooter & FPS', 'slug' => 'shooter-fps', 'color' => '#F59E0B', 'icon' => 'crosshair'],
            ['name' => 'Carreras & Conducción', 'slug' => 'carreras', 'color' => '#06B6D4', 'icon' => 'gauge'],
            ['name' => 'Terror & Survival', 'slug' => 'terror-survival', 'color' => '#EC4899', 'icon' => 'skull'],
            ['name' => 'Estrategia & Táctico', 'slug' => 'estrategia', 'color' => '#6366F1', 'icon' => 'chess-knight'],
            ['name' => 'Deportes & Simulación', 'slug' => 'deportes', 'color' => '#84CC16', 'icon' => 'trophy'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
