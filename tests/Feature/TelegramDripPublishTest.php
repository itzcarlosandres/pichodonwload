<?php

namespace Tests\Feature;

use App\Models\Console;
use App\Models\Game;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramDripPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_drip_skips_when_autopublish_disabled_without_force(): void
    {
        Setting::set('telegram_auto_publish', '0');

        $this->artisan('telegram:publish-drip')
            ->expectsOutputToContain('Despacho automático de Telegram pausado')
            ->assertExitCode(0);
    }

    public function test_telegram_drip_publishes_pending_game(): void
    {
        Setting::set('telegram_auto_publish', '1');
        Setting::set('telegram_bot_token', '123456:FAKE_TOKEN');
        Setting::set('telegram_channel_id', '@fake_channel');

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 999]], 200),
        ]);

        $console = Console::create([
            'name' => 'Game Boy Advance',
            'slug' => 'gba',
            'short_name' => 'GBA',
        ]);

        $game = Game::create([
            'title' => 'Pokemon Esmeralda',
            'slug' => 'pokemon-esmeralda',
            'console_id' => $console->id,
            'download_url' => 'https://example.com/pokemon.zip',
            'status' => 'PUBLISHED',
            'telegram_sent_at' => null,
        ]);

        $this->artisan('telegram:publish-drip', ['--limit' => 1])
            ->expectsOutputToContain('Publicado exitosamente en Telegram')
            ->assertExitCode(0);

        $this->assertNotNull($game->fresh()->telegram_sent_at);
    }
}
