<?php

namespace Tests\Feature;

use App\Models\Console;
use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_regular_users_cannot_access_admin_reviews(): void
    {
        $response = $this->get(route('admin.reviews.index'));
        $response->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'USER']);
        $response = $this->actingAs($user)->get(route('admin.reviews.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_reviews_index(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $console = Console::create([
            'name' => 'PlayStation 2',
            'slug' => 'ps2',
            'manufacturer' => 'Sony',
            'generation' => 6,
        ]);

        $game = Game::create([
            'console_id' => $console->id,
            'title' => 'God of War',
            'slug' => 'god-of-war-ps2',
            'status' => 'PUBLISHED',
        ]);

        Review::create([
            'game_id' => $game->id,
            'user_id' => $admin->id,
            'score' => 5,
            'comment' => 'Gran rendimiento en PCSX2',
            'tested_emulator' => 'PCSX2',
            'fps_performance' => '60 FPS',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reviews.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reviews.index');
        $response->assertViewHas('reviews');
        $response->assertViewHas('stats');
        $response->assertSee('Moderación de Reseñas');
        $response->assertSee('God of War');
    }

    public function test_admin_can_approve_review(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $console = Console::create([
            'name' => 'Nintendo GameCube',
            'slug' => 'gc',
            'manufacturer' => 'Nintendo',
            'generation' => 6,
        ]);

        $game = Game::create([
            'console_id' => $console->id,
            'title' => 'Zelda Wind Waker',
            'slug' => 'zelda-wind-waker-gc',
            'status' => 'PUBLISHED',
        ]);

        $review = Review::create([
            'game_id' => $game->id,
            'user_id' => $admin->id,
            'score' => 5,
            'comment' => 'Excelente juego',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reviews.approve', $review->id));

        $response->assertRedirect(route('admin.reviews.index'));
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'is_approved' => true,
        ]);
    }

    public function test_admin_can_reject_review(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $console = Console::create([
            'name' => 'Nintendo 64',
            'slug' => 'n64',
            'manufacturer' => 'Nintendo',
            'generation' => 5,
        ]);

        $game = Game::create([
            'console_id' => $console->id,
            'title' => 'Super Mario 64',
            'slug' => 'super-mario-64',
            'status' => 'PUBLISHED',
        ]);

        $review = Review::create([
            'game_id' => $game->id,
            'user_id' => $admin->id,
            'score' => 1,
            'comment' => 'Spam comment',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.reviews.reject', $review->id));

        $response->assertRedirect(route('admin.reviews.index'));
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }
}
