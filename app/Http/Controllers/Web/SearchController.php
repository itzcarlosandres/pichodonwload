<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Console;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = Game::with(['console', 'badges', 'categories'])
            ->where('status', 'PUBLISHED');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('developer', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%")
                  ->orWhere('serial', 'like', "%{$search}%")
                  ->orWhereHas('console', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('console')) {
            $query->whereHas('console', fn($q) => $q->where('slug', $request->input('console')));
        }

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

        $games = $query->paginate(24)->withQueryString();
        $consoles = Console::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])->orderBy('name')->get();
        $categories = Category::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])->orderBy('name')->get();

        return view('web.search', compact('games', 'consoles', 'categories'));
    }

    public function live(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Game::with('console')
            ->where('status', 'PUBLISHED')
            ->where(function($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('serial', 'like', "%{$q}%")
                      ->orWhere('developer', 'like', "%{$q}%")
                      ->orWhere('publisher', 'like', "%{$q}%")
                      ->orWhereHas('console', fn($cq) => $cq->where('name', 'like', "%{$q}%"));
            })
            ->take(8)
            ->get()
            ->map(function($g) {
                return [
                    'id' => $g->id,
                    'title' => $g->title,
                    'slug' => $g->slug,
                    'console' => $g->console ? $g->console->name : 'Retro',
                    'console_slug' => $g->console ? $g->console->slug : null,
                    'cover_url' => $g->cover_thumb_url ?: ($g->cover_url ?: asset('images/no-cover.png')),
                    'formatted_size' => $g->formatted_size,
                    'rating' => $g->rating_average > 0 ? number_format($g->rating_average, 1) : null,
                    'region' => $g->region ?: null,
                    'downloads' => number_format((int)$g->download_count),
                    'url' => route('game.show', $g->slug),
                ];
            });

        return response()->json($results);
    }

    public function category(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $request->merge(['category' => $category->slug]);

        $query = Game::with(['console', 'badges', 'categories'])
            ->where('status', 'PUBLISHED')
            ->whereHas('categories', fn($q) => $q->where('categories.id', $category->id));

        if ($request->filled('console')) {
            $query->whereHas('console', fn($q) => $q->where('slug', $request->input('console')));
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

        $games = $query->paginate(24)->withQueryString();
        $consoles = Console::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])->orderBy('name')->get();
        $categories = Category::withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])->orderBy('name')->get();
        $activeCategory = $category;

        return view('web.search', compact('games', 'consoles', 'categories', 'activeCategory'));
    }
}
