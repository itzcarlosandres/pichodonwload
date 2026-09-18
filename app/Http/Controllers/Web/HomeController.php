<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Console;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Spotlight & Rail games removed (Hero retired)
        $spotlightGame = null;
        $railGames = collect();

        // 1. Top 20 Consoles with count
        $consoles = Console::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])
            ->orderBy('order')
            ->get();

        // 4. Most Downloaded / Trending Games
        $trendingGames = Game::with(['console', 'badges', 'categories'])
            ->where('status', 'PUBLISHED')
            ->orderByDesc('download_count')
            ->take(12)
            ->get();

        // 5. Best Rated Games
        $topRatedGames = Game::with(['console', 'badges'])
            ->where('status', 'PUBLISHED')
            ->orderByDesc('rating_average')
            ->take(6)
            ->get();

        // 6. Recently Added Games (Configurable from Admin Panel)
        $recentCount = max(1, min(60, (int) Setting::get('home_recent_count', 12)));
        $recentOrder = Setting::get('home_recent_order', 'created_at');

        $recentQuery = Game::with(['console', 'badges', 'categories'])
            ->where('status', 'PUBLISHED');

        if ($recentOrder === 'updated_at') {
            $recentQuery->orderByDesc('updated_at');
        } elseif ($recentOrder === 'random') {
            $recentQuery->inRandomOrder();
        } else {
            $recentQuery->latest();
        }

        // Filter by console if requested from pill chips
        $selectedConsole = request('console');
        if ($selectedConsole) {
            $recentQuery->whereHas('console', fn($q) => $q->where('slug', $selectedConsole));
        }

        $recentGames = $recentQuery->take($recentCount)->get();

        // 7. Platform Global Statistics for Bottom Counter Cards
        $totalGames = Game::where('status', 'PUBLISHED')->count();
        $totalDownloads = (int) Game::where('status', 'PUBLISHED')->sum('download_count');
        $totalConsoles = $consoles->count();

        // 8. Categories for quick filters
        $categories = Category::orderBy('name')->get();

        return view('web.home', compact(
            'consoles',
            'trendingGames',
            'topRatedGames',
            'recentGames',
            'categories',
            'totalGames',
            'totalDownloads',
            'totalConsoles',
            'selectedConsole'
        ));
    }
}
