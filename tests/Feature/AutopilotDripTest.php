<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Game;
use App\Models\Console;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AutopilotDripTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_duplicate_detects_existing_game_by_title_and_console(): void
    {
        $console = Console::create([
            'name' => 'PlayStation 2',
            'slug' => 'playstation-2',
            'short_name' => 'PS2',
        ]);

        Game::create([
            'title' => 'Tekken 5',
            'slug' => 'tekken-5',
            'console_id' => $console->id,
            'download_url' => 'https://example.com/tekken5.zip',
            'file_size' => '2 GB',
            'file_format' => 'ISO',
            'status' => 'PUBLISHED',
        ]);

        $duplicate = Game::findDuplicate('Tekken 5', $console->id);
        $this->assertNotNull($duplicate);
        $this->assertEquals('Tekken 5', $duplicate->title);

        $notDuplicate = Game::findDuplicate('Crash Bandicoot', $console->id);
        $this->assertNull($notDuplicate);
    }

    public function test_publish_drip_command_publishes_draft_games(): void
    {
        $console = Console::create([
            'name' => 'PSP',
            'slug' => 'psp',
            'short_name' => 'PSP',
        ]);

        Game::create([
            'title' => 'Draft Game 1',
            'slug' => 'draft-game-1',
            'console_id' => $console->id,
            'download_url' => 'https://example.com/draft1.zip',
            'file_size' => '500 MB',
            'file_format' => 'CSO',
            'status' => 'DRAFT',
        ]);

        $this->artisan('games:publish-drip', ['--count' => 1])
            ->assertExitCode(0);

        $game = Game::where('slug', 'draft-game-1')->first();
        $this->assertEquals('PUBLISHED', $game->status);
    }

    public function test_maintenance_optimize_command_executes(): void
    {
        $this->artisan('app:maintenance-optimize')
            ->assertExitCode(0);
    }
}
