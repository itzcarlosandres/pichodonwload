<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Console;
use App\Models\User;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalGames = Game::count();
        $totalDownloads = Game::sum('download_count');
        $totalUsers = User::count();
        $pendingReviews = Review::where('is_approved', false)->count();

        $recentGames = Game::with('console')->latest()->take(5)->get();
        $recentReviews = Review::with(['game', 'user'])->latest()->take(5)->get();

        // Top 5 Most Downloaded Games
        $topGames = Game::with('console')->orderByDesc('download_count')->take(5)->get();

        // Top Consoles
        $topConsoles = Console::withCount('games')->orderByDesc('games_count')->take(6)->get();

        // Calculate storage breakdown by manufacturer
        $sonyBytes = Game::whereHas('console', fn($q) => $q->where('manufacturer', 'Sony'))->sum('file_size_bytes');
        $nintendoBytes = Game::whereHas('console', fn($q) => $q->where('manufacturer', 'Nintendo'))->sum('file_size_bytes');
        $xboxBytes = Game::whereHas('console', fn($q) => $q->where('manufacturer', 'Microsoft'))->sum('file_size_bytes');
        $segaBytes = Game::whereHas('console', fn($q) => $q->where('manufacturer', 'Sega'))->sum('file_size_bytes');
        $totalBytes = $sonyBytes + $nintendoBytes + $xboxBytes + $segaBytes ?: 1;

        $storageStats = [
            'total_gb' => round($totalBytes / 1073741824, 2),
            'sony_pct' => round(($sonyBytes / $totalBytes) * 100, 1),
            'nintendo_pct' => round(($nintendoBytes / $totalBytes) * 100, 1),
            'xbox_pct' => round(($xboxBytes / $totalBytes) * 100, 1),
            'sega_pct' => round(($segaBytes / $totalBytes) * 100, 1),
        ];

        return view('admin.dashboard', compact(
            'totalGames',
            'totalDownloads',
            'totalUsers',
            'pendingReviews',
            'recentGames',
            'recentReviews',
            'topGames',
            'topConsoles',
            'storageStats'
        ));
    }
}
