<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = auth()->user() ?? User::where('email', 'admin@romhub.io')->first();
        $favorites = $user ? $user->favorites()->with(['console', 'badges'])->paginate(12) : collect();

        return view('web.profile', compact('user', 'favorites'));
    }

    public function toggleFavorite(Request $request, Game $game): JsonResponse
    {
        $user = auth()->user() ?? User::where('email', 'admin@romhub.io')->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Inicia sesión para guardar favoritos.'], 401);
        }

        $attached = $user->favorites()->toggle($game->id);
        $isFavorited = count($attached['attached']) > 0;

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'message' => $isFavorited ? '¡Guardado en tus favoritos!' : 'Eliminado de tus favoritos',
            'count' => $user->favorites()->count(),
        ]);
    }
}
