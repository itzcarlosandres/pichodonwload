<?php

namespace Tests\Feature;

use App\Services\RomCatalogBrowserService;
use App\Services\RomScraperService;
use Tests\TestCase;

class RomsemuScraperTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Verifica que el servicio de scraping reconozca y procese URLs de romsemu.com
     */
    public function test_romsemu_scraper_recognizes_romsemu_domain(): void
    {
        $scraper = app(RomScraperService::class);
        $result = $scraper->scrape('https://romsemu.com/nintendo-switch/super-mario-odyssey/');

        $this->assertTrue($result['success']);
        $this->assertEquals('Romsemu', $result['source_provider']);
        $this->assertEquals('Nintendo Switch', $result['platform']);
        $this->assertEquals('nintendo-switch', $result['platform_slug']);
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['download_url']);
        // NUNCA debe contener URLs intermedias de romsemu.com
        $this->assertStringNotContainsString('romsemu.com', $result['download_url']);
        $this->assertIsArray($result['download_links']);
        foreach ($result['download_links'] as $link) {
            $this->assertStringNotContainsString('romsemu.com', $link['url']);
            $this->assertContains($link['server'], ['1Fichier', 'Mega', 'MediaFire', 'Google Drive', 'PixelDrain', 'Torrent', 'Descarga Alternativa']);
        }
    }

    /**
     * Verifica que el explorador de catálogo soporte romsemu y devuelva juegos
     */
    public function test_romsemu_catalog_browser_returns_games(): void
    {
        $browser = app(RomCatalogBrowserService::class);
        $result = $browser->browse('romsemu', 'nintendo-switch', 1);

        $this->assertTrue($result['success']);
        $this->assertEquals('romsemu', $result['provider']);
        $this->assertIsArray($result['games']);
        $this->assertNotEmpty($result['games']);
        $this->assertArrayHasKey('title', $result['games'][0]);
        $this->assertArrayHasKey('url', $result['games'][0]);
    }

    /**
     * Verifica que el comando de auto-cosecha acepte el proveedor romsemu
     */
    public function test_auto_harvest_accepts_romsemu_provider(): void
    {
        $this->artisan('roms:auto-harvest', [
            '--provider' => 'romsemu',
            '--console' => 'nintendo-switch',
            '--limit' => 1,
            '--dry-run' => true,
        ])->assertExitCode(0);
    }
}
