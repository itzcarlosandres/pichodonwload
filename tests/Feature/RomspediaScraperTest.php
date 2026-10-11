<?php

namespace Tests\Feature;

use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use App\Services\RomCatalogBrowserService;
use App\Services\RomScraperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RomspediaScraperTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que el servicio de scraping extraiga el enlace directo de Romspedia
     * y popule tanto direct_download_url como download_links estructurados.
     */
    public function test_romspedia_scraper_extracts_direct_download_and_links(): void
    {
        $scraper = app(RomScraperService::class);
        $result = $scraper->scrape('https://www.romspedia.com/roms/nintendo-64/super-mario-64-original');

        $this->assertTrue($result['success']);
        $this->assertEquals('Romspedia', $result['source_provider']);
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['direct_download_url']);
        $this->assertStringContainsString('downloads.romspedia.com', $result['direct_download_url']);
        $this->assertNotEmpty($result['download_url']);
        $this->assertEquals($result['direct_download_url'], $result['download_url']);
        // No debe duplicar el enlace principal dentro de download_links
        $this->assertEmpty($result['download_links']);
    }

    /**
     * Verifica que el explorador de catálogo liste juegos correctamente desde Romspedia.
     */
    public function test_romspedia_catalog_browser_returns_games(): void
    {
        $browser = app(RomCatalogBrowserService::class);
        $result = $browser->browse('romspedia', 'nintendo-64', 1);

        $this->assertTrue($result['success']);
        $this->assertEquals('romspedia', $result['provider']);
        $this->assertIsArray($result['games']);
        $this->assertNotEmpty($result['games']);
        $this->assertArrayHasKey('title', $result['games'][0]);
        $this->assertArrayHasKey('url', $result['games'][0]);
        $this->assertStringContainsString('romspedia.com', $result['games'][0]['url']);
    }

    /**
     * Verifica que al guardar en catálogo NO se eliminen los enlaces de downloads.romspedia.com
     * y tampoco se dupliquen como mirror adicional si ya son el servidor principal.
     */
    public function test_admin_controller_preserves_romspedia_direct_download_links(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $console = Console::create([
            'name' => 'Nintendo 64',
            'slug' => 'nintendo-64',
            'manufacturer' => 'Nintendo',
        ]);

        $dlUrl = 'https://downloads.romspedia.com/roms/Super%20Mario%2064%20(USA).zip';
        $mirrorUrl = 'https://1fichier.com/?altmirror123';

        $response = $this->actingAs($admin)->postJson('/admin/scraper-demo/save', [
            'title' => 'Super Mario 64 Test',
            'console_id' => $console->id,
            'download_url' => $dlUrl,
            'file_size' => '5.9 MB',
            'file_format' => 'ZIP',
            'download_links' => [
                // Enlace idéntico al principal (debe descartarse para no duplicar)
                [
                    'server' => 'Mega',
                    'url' => $dlUrl,
                    'size' => '5.9 MB',
                ],
                // Mirror adicional legítimo
                [
                    'server' => '1Fichier',
                    'url' => $mirrorUrl,
                    'size' => '5.9 MB',
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $saved = Game::where('title', 'Super Mario 64 Test')->first();
        $this->assertNotNull($saved);
        $this->assertEquals($dlUrl, $saved->download_url);
        // Debe tener solo el mirror adicional y NO el duplicado
        $this->assertCount(1, $saved->download_links);
        $this->assertEquals($mirrorUrl, $saved->download_links[0]['url']);
        $this->assertEquals('1Fichier', $saved->download_links[0]['server']);
    }
}
