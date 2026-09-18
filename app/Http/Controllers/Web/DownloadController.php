<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DownloadController extends Controller
{
    /**
     * Muestra la página dedicada y enriquecida de descarga del videojuego
     */
    public function show(string $slug): View
    {
        $query = Game::with(['console', 'categories', 'badges'])->where('slug', $slug);

        if (!auth()->check() || !auth()->user()->isAdmin()) {
            $query->where('status', 'PUBLISHED');
        }

        $game = $query->firstOrFail();

        // Increment views count on download page
        $game->increment('views_count');

        // Obtener juegos relacionados de la misma consola
        $relatedGames = Game::with('console')
            ->where('console_id', $game->console_id)
            ->where('id', '!=', $game->id)
            ->where('status', 'PUBLISHED')
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('web.download', compact('game', 'relatedGames'));
    }

    /**
     * Registra e incrementa el contador de descargas vía AJAX
     */
    public function track(string $slug): JsonResponse
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $game->increment('download_count');

        return response()->json([
            'success' => true,
            'download_count' => $game->download_count,
        ]);
    }
}
