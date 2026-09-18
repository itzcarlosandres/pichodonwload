<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Console;
use App\Models\Category;
use App\Models\Badge;
use App\Models\Banner;
use App\Models\Screenshot;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $ps2 = Console::where('slug', 'playstation-2')->first();
        $switch = Console::where('slug', 'nintendo-switch')->first();
        $gcn = Console::where('slug', 'gamecube')->first();
        $x360 = Console::where('slug', 'xbox-360')->first();
        $gba = Console::where('slug', 'game-boy-advance')->first();
        $dc = Console::where('slug', 'dreamcast')->first();

        $catAccion = Category::where('slug', 'accion-aventura')->first();
        $catRpg = Category::where('slug', 'rpg-jrpg')->first();
        $catPlataformas = Category::where('slug', 'plataformas')->first();
        $catLucha = Category::where('slug', 'lucha')->first();
        $catShooter = Category::where('slug', 'shooter-fps')->first();

        $badge60fps = Badge::where('slug', 'verificado-60-fps')->first();
        $badgeNoIntro = Badge::where('slug', 'no-intro-verified')->first();
        $badgeRedump = Badge::where('slug', 'redump-clean')->first();
        $badgeEsp = Badge::where('slug', 'traduccion-espanol')->first();
        $badgeMes = Badge::where('slug', 'juego-del-mes')->first();

        // 1. Shadow of the Colossus (PS2) - Spotlight Game
        if ($ps2) {
            $game1 = Game::updateOrCreate(['slug' => 'shadow-of-the-colossus-ps2'], [
                'title' => 'Shadow of the Colossus',
                'slug' => 'shadow-of-the-colossus-ps2',
                'console_id' => $ps2->id,
                'cover_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1600&q=80',
                'description' => "Una obra maestra atemporal de Fumito Ueda y Team Ico. Viaja por una tierra olvidada a lomos de tu fiel corcel Agro y derrota a 16 colosos legendarios para devolver la vida a una joven sacrificada.\n\n### Rendimiento en Emulación (PCSX2)\n- Configuración recomendada: Renderer Vulkan, 3x Resolución Nativa (1080p).\n- Compatible con widescreen patch 16:9 y texturas HD escaladas sin caídas de framerate.",
                'release_year' => 2005,
                'developer' => 'Team Ico / SCE Japan Studio',
                'publisher' => 'Sony Computer Entertainment',
                'serial' => 'SCUS-97472',
                'region' => 'NTSC-U',
                'languages' => 'Español, Inglés',
                'file_size_bytes' => 3661627392, // 3.41 GB
                'file_format' => 'CHD',
                'download_url' => 'https://storage.romhub.io/ps2/shadow_of_the_colossus_usa.chd',
                'crc32' => '4A8B991F',
                'sha256' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                'meta_title' => 'Descargar Shadow of the Colossus PS2 ROM ISO Español 100% Verificado',
                'meta_description' => 'Descarga Shadow of the Colossus para PlayStation 2 en formato CHD/ISO verificado. Compatible con PCSX2 a 60 FPS y gráficos 4K.',
                'download_count' => 142500,
                'views_count' => 320000,
                'rating_average' => 4.95,
                'rating_count' => 3820,
                'status' => 'PUBLISHED',
                'is_spotlight' => true,
                'is_featured' => true,
            ]);

            if ($catAccion) $game1->categories()->syncWithoutDetaching([$catAccion->id]);
            if ($badge60fps && $badgeRedump && $badgeMes) {
                $game1->badges()->syncWithoutDetaching([$badge60fps->id, $badgeRedump->id, $badgeMes->id]);
            }

            // Banner
            Banner::updateOrCreate(['title' => 'Shadow of the Colossus'], [
                'title' => 'Shadow of the Colossus',
                'subtitle' => 'Juego Destacado del Mes • PlayStation 2 • 1080p Enhanced',
                'image_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1600&q=80',
                'target_url' => '/game/shadow-of-the-colossus-ps2',
                'badge_text' => 'JUEGO DEL MES',
                'is_active' => true,
                'order' => 1,
            ]);
        }

        // 2. Zelda: Breath of the Wild (Switch)
        if ($switch) {
            $game2 = Game::updateOrCreate(['slug' => 'the-legend-of-zelda-breath-of-the-wild'], [
                'title' => 'The Legend of Zelda: Breath of the Wild',
                'slug' => 'the-legend-of-zelda-breath-of-the-wild',
                'console_id' => $switch->id,
                'cover_url' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1600&q=80',
                'description' => "Despierta tras un sueño de 100 años y explora un reino de Hyrule en ruinas en la mayor aventura de mundo abierto de Nintendo. Incluye juego base v1.0.0, actualización v1.6.0 y los dos paquetes de DLC The Master Trials y The Champions' Ballad.",
                'release_year' => 2017,
                'developer' => 'Nintendo EPD',
                'publisher' => 'Nintendo',
                'serial' => '01007EF00011E000',
                'region' => 'Global',
                'languages' => 'Español, Inglés, Francés, Japonés',
                'file_size_bytes' => 14408925184, // 13.42 GB
                'file_format' => 'NSP',
                'download_url' => 'https://storage.romhub.io/switch/zelda_botw_base_update.nsp',
                'crc32' => 'A8F290BD',
                'sha256' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
                'meta_title' => 'Descargar Zelda Breath of the Wild Switch ROM NSP Español + DLC',
                'meta_description' => 'ROM NSP limpia de The Legend of Zelda: Breath of the Wild para Nintendo Switch. Compatible con Ryujinx a 60 FPS.',
                'download_count' => 189200,
                'views_count' => 450000,
                'rating_average' => 4.90,
                'rating_count' => 4120,
                'status' => 'PUBLISHED',
                'is_spotlight' => false,
                'is_featured' => true,
            ]);

            if ($catAccion && $catRpg) $game2->categories()->syncWithoutDetaching([$catAccion->id, $catRpg->id]);
            if ($badgeNoIntro && $badgeEsp) $game2->badges()->syncWithoutDetaching([$badgeNoIntro->id, $badgeEsp->id]);
        }

        // 3. God of War II (PS2)
        if ($ps2) {
            $game3 = Game::updateOrCreate(['slug' => 'god-of-war-ii-ps2'], [
                'title' => 'God of War II',
                'slug' => 'god-of-war-ii-ps2',
                'console_id' => $ps2->id,
                'cover_url' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=800&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=1600&q=80',
                'description' => "Kratos busca cambiar su destino desafiando a las mismísimas Moiras. Una de las mayores obras cumbre del género Hack and Slash en la era de los 128 bits con gráficos que llevaron a PS2 a su límite absoluto.",
                'release_year' => 2007,
                'developer' => 'Santa Monica Studio',
                'publisher' => 'Sony Computer Entertainment',
                'serial' => 'SLUS-21586',
                'region' => 'PAL / NTSC',
                'languages' => 'Español, Inglés, Francés, Alemán, Italiano',
                'file_size_bytes' => 8482885632, // 7.9 GB
                'file_format' => 'ISO',
                'download_url' => 'https://storage.romhub.io/ps2/god_of_war_2_dvd9.iso',
                'crc32' => 'B71A390C',
                'sha256' => '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8',
                'meta_title' => 'Descargar God of War II PS2 ISO Español Voces y Textos 100% Funcional',
                'meta_description' => 'Descarga God of War 2 para PlayStation 2 en formato ISO DVD9 completo en español.',
                'download_count' => 98200,
                'views_count' => 210000,
                'rating_average' => 4.80,
                'rating_count' => 2890,
                'status' => 'PUBLISHED',
                'is_spotlight' => false,
                'is_featured' => true,
            ]);

            if ($catAccion) $game3->categories()->syncWithoutDetaching([$catAccion->id]);
            if ($badgeRedump && $badgeEsp) $game3->badges()->syncWithoutDetaching([$badgeRedump->id, $badgeEsp->id]);
        }

        // 4. Super Smash Bros. Melee (GameCube)
        if ($gcn) {
            $game4 = Game::updateOrCreate(['slug' => 'super-smash-bros-melee'], [
                'title' => 'Super Smash Bros. Melee',
                'slug' => 'super-smash-bros-melee',
                'console_id' => $gcn->id,
                'cover_url' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=800&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=1600&q=80',
                'description' => "La cumbre competitiva de la franquicia Super Smash Bros. Con físicas rápidas, técnicas avanzadas de movimiento (wavedash, l-canceling) y compatibilidad perfecta con Slippi para juego online rollback.",
                'release_year' => 2001,
                'developer' => 'HAL Laboratory',
                'publisher' => 'Nintendo',
                'serial' => 'GALE01',
                'region' => 'NTSC-U',
                'languages' => 'Inglés',
                'file_size_bytes' => 1449656320, // 1.35 GB
                'file_format' => 'RVZ',
                'download_url' => 'https://storage.romhub.io/gcn/super_smash_bros_melee_v1.02.rvz',
                'crc32' => 'D73F839A',
                'sha256' => '4b227777d4dd1fc61c6f884f48641d02b4d121d3fd328cb08b5531fcacdabf8a',
                'meta_title' => 'Descargar Super Smash Bros Melee GameCube ISO RVZ Slippi Clean v1.02',
                'meta_description' => 'ROM oficial v1.02 de Super Smash Bros Melee para GameCube y Dolphin/Slippi.',
                'download_count' => 210400,
                'views_count' => 490000,
                'rating_average' => 4.90,
                'rating_count' => 5200,
                'status' => 'PUBLISHED',
                'is_spotlight' => false,
                'is_featured' => true,
            ]);

            if ($catLucha) $game4->categories()->syncWithoutDetaching([$catLucha->id]);
            if ($badgeRedump && $badge60fps) $game4->badges()->syncWithoutDetaching([$badgeRedump->id, $badge60fps->id]);
        }

        // 5. Halo 3 (Xbox 360)
        if ($x360) {
            $game5 = Game::updateOrCreate(['slug' => 'halo-3-xbox-360'], [
                'title' => 'Halo 3: Finish The Fight',
                'slug' => 'halo-3-xbox-360',
                'console_id' => $x360->id,
                'cover_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1600&q=80',
                'description' => "Master Chief concluye la trilogía original en una batalla desesperada por salvar la Tierra y la galaxia entera del Covenant y los Flood. Compatible con Xenia Canary.",
                'release_year' => 2007,
                'developer' => 'Bungie',
                'publisher' => 'Microsoft Game Studios',
                'serial' => 'MS-4D5307E6',
                'region' => 'Region Free',
                'languages' => 'Español Latino, Español España, Inglés',
                'file_size_bytes' => 7838315520, // 7.3 GB
                'file_format' => 'GOD',
                'download_url' => 'https://storage.romhub.io/x360/halo_3_god.zip',
                'crc32' => '8F012B9C',
                'sha256' => 'ef2d127de37b942baad06145e54b0c619a1f22327b2ebbcfbec78f5564afe39d',
                'meta_title' => 'Descargar Halo 3 Xbox 360 GOD ISO Español Completo',
                'meta_description' => 'Descarga Halo 3 para Xbox 360 en formato GOD e ISO compatible con RGH y emulador Xenia.',
                'download_count' => 89100,
                'views_count' => 190000,
                'rating_average' => 4.85,
                'rating_count' => 2400,
                'status' => 'PUBLISHED',
                'is_spotlight' => false,
                'is_featured' => true,
            ]);

            if ($catShooter) $game5->categories()->syncWithoutDetaching([$catShooter->id]);
            if ($badgeEsp) $game5->badges()->syncWithoutDetaching([$badgeEsp->id]);
        }

        // 6. Pokemon Emerald (GBA)
        if ($gba) {
            $game6 = Game::updateOrCreate(['slug' => 'pokemon-esmeralda-gba'], [
                'title' => 'Pokémon Edición Esmeralda (v1.1)',
                'slug' => 'pokemon-esmeralda-gba',
                'console_id' => $gba->id,
                'cover_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1600&q=80',
                'description' => "La edición definitiva de la tercera generación en la región de Hoenn. Enfréntate tanto al Team Magma como al Team Aqua y despierta a Rayquaza para calmar la batalla entre Groudon y Kyogre.",
                'release_year' => 2004,
                'developer' => 'Game Freak',
                'publisher' => 'The Pokémon Company / Nintendo',
                'serial' => 'AGB-BPEE-USA',
                'region' => 'España / PAL',
                'languages' => 'Español',
                'file_size_bytes' => 16777216, // 16 MB
                'file_format' => 'GBA',
                'download_url' => 'https://storage.romhub.io/gba/pokemon_esmeralda_esp.gba',
                'crc32' => '1F1C08FB',
                'sha256' => '876b5d9be11f5d6ff73df78198f480ad9be79c67b2d2946c5521b44cbcf28e18',
                'meta_title' => 'Descargar Pokémon Esmeralda GBA ROM Español Limpia No-Intro',
                'meta_description' => 'ROM 100% en español de Pokémon Esmeralda para Game Boy Advance y emulador mGBA.',
                'download_count' => 320100,
                'views_count' => 670000,
                'rating_average' => 4.95,
                'rating_count' => 6800,
                'status' => 'PUBLISHED',
                'is_spotlight' => false,
                'is_featured' => true,
            ]);

            if ($catRpg) $game6->categories()->syncWithoutDetaching([$catRpg->id]);
            if ($badgeNoIntro && $badgeEsp) $game6->badges()->syncWithoutDetaching([$badgeNoIntro->id, $badgeEsp->id]);
        }
    }
}
