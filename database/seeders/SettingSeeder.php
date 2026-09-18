<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name', 'value' => 'ROMHUB', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Preservación Digital y Biblioteca de Videojuegos', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'contact@romhub.io', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'general'],

            // Storage S3 / Cloudflare R2
            ['key' => 'storage_driver', 'value' => 'local', 'group' => 'storage'], // local / r2 / s3
            ['key' => 'r2_endpoint', 'value' => '', 'group' => 'storage'],
            ['key' => 'r2_bucket', 'value' => 'romhub-production-vault', 'group' => 'storage'],
            ['key' => 'r2_access_key', 'value' => '', 'group' => 'storage'],
            ['key' => 'r2_secret_key', 'value' => '', 'group' => 'storage'],
            ['key' => 'r2_public_url', 'value' => '', 'group' => 'storage'],

            // SEO
            ['key' => 'seo_meta_title', 'value' => 'ROMHUB — Biblioteca Premium de Videojuegos y Preservación', 'group' => 'seo'],
            ['key' => 'seo_meta_description', 'value' => 'Explora, descarga y preserva más de 25,000 títulos clásicos y modernos organizados por 20 consolas con hashes verificados y guías de emulación.', 'group' => 'seo'],
            ['key' => 'seo_keywords', 'value' => 'roms, emulacion, videojuegos retro, ps2, switch, gamecube, xbox 360, gba, no-intro, redump', 'group' => 'seo'],

            // AI Configuration
            ['key' => 'ai_provider', 'value' => 'gemini', 'group' => 'ai'], // gemini / openai
            ['key' => 'ai_api_key', 'value' => '', 'group' => 'ai'],
            ['key' => 'ai_model', 'value' => 'gemini-1.5-flash', 'group' => 'ai'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
