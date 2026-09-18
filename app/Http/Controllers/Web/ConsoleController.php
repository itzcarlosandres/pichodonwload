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
        $consoles = Console::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])
            ->orderBy('order')
            ->get();

        return view('web.consoles-hub', compact('consoles'));
    }

    public function show(Request $request, string $slug): View
    {
        $console = Console::where('slug', $slug)->firstOrFail();

        $query = Game::with(['badges', 'categories'])
            ->where('console_id', $console->id)
            ->where('status', 'PUBLISHED');

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
