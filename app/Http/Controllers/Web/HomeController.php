<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Console;
use App\Models\Game;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Spotlight & Rail games removed (Hero retired)
        $spotlightGame = null;
        $railGames = collect();

        // 1. Top Consoles with published and total uploaded count
        $consoles = Console::withCount([
            'games as games_count' => fn ($q) => $q->whereIn('status', ['PUBLISHED', 'published']),
            'games as total_uploaded_count' => fn ($q) => $q->whereIn('status', ['PUBLISHED', 'published', 'DRAFT', 'draft']),
        ])
            ->orderBy('order')
            ->get();

        $publishedCount = Game::whereIn('status', ['PUBLISHED', 'published'])->count();
        $allowedStatuses = $publishedCount > 0
            ? ['PUBLISHED', 'published']
            : ['PUBLISHED', 'published', 'DRAFT', 'draft'];

        // 4. Most Downloaded / Trending Games
        $trendingGames = Game::with(['console', 'badges', 'categories'])
            ->whereIn('status', $allowedStatuses)
            ->orderByDesc('download_count')
            ->take(12)
            ->get();

        // 5. Best Rated Games
        $topRatedGames = Game::with(['console', 'badges'])
            ->whereIn('status', $allowedStatuses)
            ->orderByDesc('rating_average')
            ->take(6)
            ->get();

        // 6. Recently Added Games (Configurable from Admin Panel)
        $recentCount = max(1, min(60, (int) Setting::get('home_recent_count', 12)));
        $recentOrder = Setting::get('home_recent_order', 'created_at');

        $recentQuery = Game::with(['console', 'badges', 'categories'])
            ->whereIn('status', $allowedStatuses);

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
            $recentQuery->whereHas('console', fn ($q) => $q->where('slug', $selectedConsole));
        }

        $recentGames = $recentQuery->take($recentCount)->get();

        // 7. Platform Global Statistics for Bottom Counter Cards
        $totalGames = Game::whereIn('status', $allowedStatuses)->count();
        $totalDownloads = (int) Game::whereIn('status', $allowedStatuses)->sum('download_count');
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
