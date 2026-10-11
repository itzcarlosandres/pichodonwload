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
        $this->assertNotEmpty($result['download_links']);
        $this->assertIsArray($result['download_links']);
        $this->assertEquals('Romspedia Direct', $result['download_links'][0]['server']);
        $this->assertStringContainsString('downloads.romspedia.com', $result['download_links'][0]['url']);
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
     * Verifica que al guardar en catálogo NO se eliminen ni anulen los enlaces de downloads.romspedia.com
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

        $response = $this->actingAs($admin)->postJson('/admin/scraper-demo/save', [
            'title' => 'Super Mario 64 Test',
            'console_id' => $console->id,
            'download_url' => $dlUrl,
            'file_size' => '5.9 MB',
            'file_format' => 'ZIP',
            'download_links' => [
                [
                    'server' => 'Romspedia Direct',
                    'url' => $dlUrl,
                    'size' => '5.9 MB',
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $saved = Game::where('title', 'Super Mario 64 Test')->first();
        $this->assertNotNull($saved);
        $this->assertEquals($dlUrl, $saved->download_url);
        $this->assertNotEmpty($saved->download_links);
        $this->assertEquals($dlUrl, $saved->download_links[0]['url']);
        $this->assertEquals('Romspedia Direct', $saved->download_links[0]['server']);
    }
}
