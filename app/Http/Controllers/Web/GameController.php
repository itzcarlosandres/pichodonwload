<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Review;
use App\Services\SeoInterlinkService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GameController extends Controller
{
    public function show(string $slug): View
    {
        $game = Game::with(['console', 'badges', 'categories', 'screenshots', 'reviews.user', 'franchises'])
            ->where('slug', $slug)
            ->where('status', 'PUBLISHED')
            ->firstOrFail();

        // Increments views count
        $game->increment('views_count');

        $console = $game->console;

        // Automatically match the active recommended emulator published by admin
        $recommendedEmulator = null;
        if ($console) {
            // 1. Check if console's recommended_emulator matches an active emulator name or system
            if (!empty($console->recommended_emulator)) {
                $cleanRec = trim($console->recommended_emulator);
                $recommendedEmulator = \App\Models\Emulator::where('is_active', true)
                    ->where(function($q) use ($cleanRec) {
                        $q->where('name', 'like', "%{$cleanRec}%")
                          ->orWhere('system', 'like', "%{$cleanRec}%");
                    })
                    ->orderBy('order')
                    ->first();
            }

            // 2. Match by console name, short_name or slug in emulator system/description
            if (!$recommendedEmulator) {
                $name = $console->name;
                $shortName = $console->short_name;
                $slug = $console->slug;

                $recommendedEmulator = \App\Models\Emulator::where('is_active', true)
                    ->where(function($q) use ($name, $shortName, $slug) {
                        $q->where('system', 'like', "%{$name}%");
                        if (!empty($shortName)) {
                            $q->orWhere('system', 'like', "%{$shortName}%");
                        }
                    })
                    ->orderBy('order')
                    ->first();
            }

            // 3. Fallback to Multi-system emulator (e.g. RetroArch) published by admin
            if (!$recommendedEmulator) {
                $recommendedEmulator = \App\Models\Emulator::where('is_active', true)
                    ->where(function($q) {
                        $q->where('system', 'like', '%Multi%')
                          ->orWhere('name', 'like', '%RetroArch%');
                    })
                    ->orderBy('order')
                    ->first();
            }
        }

        // Automatically match the active required BIOS published by admin
        $requiredBios = null;
        if ($console) {
            $requiredBios = \App\Models\Bios::where('is_active', true)
                ->where(function($q) use ($console) {
                    $q->where('console_id', $console->id)
                      ->orWhere('console_slug', $console->slug)
                      ->orWhere('system', 'like', "%{$console->name}%");
                    if (!empty($console->short_name)) {
                        $q->orWhere('system', 'like', "%{$console->short_name}%");
                    }
                })
                ->orderBy('order')
                ->first();
        }

        // Related Games from the same console or genre
        $relatedGames = Game::with(['console', 'badges'])
            ->where('status', 'PUBLISHED')
            ->where('console_id', $game->console_id)
            ->where('id', '!=', $game->id)
            ->take(4)
            ->get();

        $interlinkService = app(SeoInterlinkService::class);

        return view('web.game-detail', compact('game', 'relatedGames', 'recommendedEmulator', 'requiredBios', 'interlinkService'));
    }

    public function submitReview(Request $request, Game $game): RedirectResponse
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:1000',
            'author_name' => 'nullable|string|max:100',
            'tested_hardware' => 'nullable|string|max:100',
            'tested_emulator' => 'nullable|string|max:100',
            'fps_performance' => 'nullable|string|max:30',
        ]);

        $user = auth()->user();
        $authorName = $user ? $user->name : (!empty($validated['author_name']) ? trim($validated['author_name']) : 'Jugador de la Comunidad');

        if ($user) {
            Review::updateOrCreate(
                ['game_id' => $game->id, 'user_id' => $user->id],
                [
                    'author_name' => $authorName,
                    'score' => $validated['score'],
                    'comment' => $validated['comment'],
                    'tested_hardware' => $validated['tested_hardware'] ?? null,
                    'tested_emulator' => $validated['tested_emulator'] ?? null,
                    'fps_performance' => $validated['fps_performance'] ?? null,
                    'is_approved' => true,
                ]
            );
        } else {
            Review::create([
                'game_id' => $game->id,
                'user_id' => null,
                'author_name' => $authorName,
                'score' => $validated['score'],
                'comment' => $validated['comment'],
                'tested_hardware' => $validated['tested_hardware'] ?? null,
                'tested_emulator' => $validated['tested_emulator'] ?? null,
                'fps_performance' => $validated['fps_performance'] ?? null,
                'is_approved' => true,
            ]);
        }

        // Recalculate average rating
        $avg = $game->reviews()->where('is_approved', true)->avg('score');
        $count = $game->reviews()->where('is_approved', true)->count();
        $game->update([
            'rating_average' => round($avg ?: 5.0, 2),
            'rating_count' => $count,
        ]);

        return back()->with('success', '¡Tu reporte de rendimiento y valoración han sido publicados con éxito!');
    }
}
