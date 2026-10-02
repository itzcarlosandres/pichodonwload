<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bios;
use App\Models\Console;
use App\Models\Emulator;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    /**
     * Muestra la Biblioteca Centralizada: ROMs Recientes, Emuladores y Archivos BIOS
     */
    public function index(Request $request): View
    {
        $currentTab = $request->input('tab', 'games');
        if (! in_array($currentTab, ['games', 'emulators', 'bios'])) {
            $currentTab = 'games';
        }

        $publishedCount = Game::whereIn('status', ['PUBLISHED', 'published'])->count();
        $allowedStatuses = $publishedCount > 0
            ? ['PUBLISHED', 'published']
            : ['PUBLISHED', 'published', 'DRAFT', 'draft'];

        // 1. Catálogo de ROMs & Videojuegos Recientes
        $selectedConsole = $request->input('console');
        $selectedSort = $request->input('sort', 'recent');
        $searchQuery = trim($request->input('q', ''));

        $gamesQuery = Game::with(['console', 'badges', 'categories'])
            ->whereIn('status', $allowedStatuses);

        if (! empty($searchQuery)) {
            $gamesQuery->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                    ->orWhere('developer', 'like', "%{$searchQuery}%")
                    ->orWhere('publisher', 'like', "%{$searchQuery}%");
            });
        }

        if (! empty($selectedConsole)) {
            $gamesQuery->whereHas('console', fn ($q) => $q->where('slug', $selectedConsole));
        }

        switch ($selectedSort) {
            case 'downloads':
                $gamesQuery->orderByDesc('download_count');
                break;
            case 'rating':
                $gamesQuery->orderByDesc('rating_average')->orderByDesc('rating_count');
                break;
            case 'name_asc':
                $gamesQuery->orderBy('title', 'asc');
                break;
            case 'name_desc':
                $gamesQuery->orderBy('title', 'desc');
                break;
            case 'oldest':
                $gamesQuery->oldest();
                break;
            case 'recent':
            default:
                $gamesQuery->latest();
                break;
        }

        $games = $gamesQuery->paginate(24)->withQueryString();

        // 2. Directorio de Emuladores Oficiales
        $emulators = Emulator::where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        // 3. Directorio de Archivos BIOS & Firmware
        $biosList = Bios::where('is_active', true)
            ->with('console')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        // Consolas con juegos disponibles para la botonera de filtrado
        $consoles = Console::whereHas('games', fn ($q) => $q->whereIn('status', $allowedStatuses))
            ->withCount(['games' => fn ($q) => $q->whereIn('status', $allowedStatuses)])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $totalGamesCount = Game::whereIn('status', $allowedStatuses)->count();

        return view('web.library', compact(
            'currentTab',
            'games',
            'emulators',
            'biosList',
            'consoles',
            'selectedConsole',
            'selectedSort',
            'searchQuery',
            'totalGamesCount'
        ));
    }
}
