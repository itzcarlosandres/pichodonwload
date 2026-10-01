<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Console;
use App\Models\Game;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsoleController extends Controller
{
    public function index(): View
    {
        $consoles = Console::withCount([
            'games as games_count' => fn($q) => $q->whereIn('status', ['PUBLISHED', 'published']),
            'games as total_uploaded_count' => fn($q) => $q->whereIn('status', ['PUBLISHED', 'published', 'DRAFT', 'draft']),
        ])
        ->orderBy('order')
        ->get();

        return view('web.consoles-hub', compact('consoles'));
    }

    public function show(Request $request, string $slug): View
    {
        $cleanSlug = strtolower(trim($slug));
        $aliases = [
            'ps4' => 'playstation-4',
            'playstation-4' => 'ps4',
            'ps3' => 'playstation-3',
            'playstation-3' => 'ps3',
            'ps2' => 'playstation-2',
            'playstation-2' => 'ps2',
            'ps1' => 'playstation',
            'playstation' => 'ps1',
            'psp' => 'playstation-portable',
            'playstation-portable' => 'psp',
            'psvita' => 'playstation-vita',
            'playstation-vita' => 'psvita',
            'gba' => 'game-boy-advance',
            'game-boy-advance' => 'gba',
            'gb' => 'game-boy',
            'game-boy' => 'gb',
            '3ds' => 'nintendo-3ds',
            'nintendo-3ds' => '3ds',
            'ds' => 'nintendo-ds',
            'nintendo-ds' => 'ds',
            'switch' => 'nintendo-switch',
            'nintendo-switch' => 'switch',
            'n64' => 'nintendo-64',
            'nintendo-64' => 'n64',
            'snes' => 'super-nintendo',
            'super-nintendo' => 'snes',
        ];

        $targetSlugs = array_values(array_unique(array_filter([
            $slug,
            $cleanSlug,
            $aliases[$cleanSlug] ?? null,
            str_replace('_', '-', $cleanSlug),
        ])));

        $console = Console::whereIn('slug', $targetSlugs)
            ->orWhereRaw('LOWER(slug) = ?', [$cleanSlug])
            ->first();

        if (!$console) {
            $console = Console::whereRaw('LOWER(name) = ?', [str_replace('-', ' ', $cleanSlug)])
                ->orWhere('slug', 'like', "%{$cleanSlug}%")
                ->firstOrFail();
        }

        // Catch all console IDs that correspond to this platform (e.g. ps4 + playstation-4)
        $matchingConsoleIds = Console::whereIn('slug', $targetSlugs)
            ->orWhereRaw('LOWER(slug) = ?', [$cleanSlug])
            ->orWhereRaw('LOWER(name) = ?', [strtolower(trim($console->name))])
            ->pluck('id')
            ->toArray();

        if (empty($matchingConsoleIds)) {
            $matchingConsoleIds = [$console->id];
        }

        $publishedCount = Game::whereIn('console_id', $matchingConsoleIds)
            ->whereIn('status', ['PUBLISHED', 'published'])
            ->count();

        $allowedStatuses = $publishedCount > 0
            ? ['PUBLISHED', 'published']
            : ['PUBLISHED', 'published', 'DRAFT', 'draft'];

        $query = Game::with(['badges', 'categories'])
            ->whereIn('console_id', $matchingConsoleIds)
            ->whereIn('status', $allowedStatuses);

        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('slug', $request->input('category')));
        }

        if ($request->filled('region')) {
            $query->where('region', 'like', "%{$request->input('region')}%");
        }

        $sort = $request->input('sort', 'popular');
        if ($sort === 'rating') {
            $query->orderByDesc('rating_average');
        } elseif ($sort === 'name') {
            $query->orderBy('title');
        } elseif ($sort === 'latest') {
            $query->latest();
        } else {
            $query->orderByDesc('download_count');
        }

        $games = $query->paginate(18)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('web.console-detail', compact('console', 'games', 'categories'));
    }
}
