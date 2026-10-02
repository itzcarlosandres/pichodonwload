<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Console;
use App\Models\Game;
use App\Models\SearchLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = Game::with(['console', 'badges', 'categories']);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('developer', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%")
                    ->orWhere('serial', 'like', "%{$search}%")
                    ->orWhereHas('console', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('console')) {
            $consoleSlug = (string) $request->input('console');
            $cleanConsole = strtolower(trim($consoleSlug));
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
            ];
            $slugs = array_values(array_unique(array_filter([
                $consoleSlug,
                $cleanConsole,
                $aliases[$cleanConsole] ?? null,
                str_replace('-', ' ', $cleanConsole),
            ])));

            $query->whereHas('console', function ($q) use ($slugs, $cleanConsole) {
                $q->whereIn('slug', $slugs)
                    ->orWhereRaw('LOWER(slug) = ?', [$cleanConsole])
                    ->orWhereRaw('LOWER(name) = ?', [str_replace('-', ' ', $cleanConsole)]);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $request->input('category')));
        }

        if ($request->filled('region')) {
            $query->where('region', 'like', "%{$request->input('region')}%");
        }

        // Fallback to draft games if no published games exist yet
        $hasPublished = (clone $query)->whereIn('status', ['PUBLISHED', 'published'])->exists();
        if ($hasPublished) {
            $query->whereIn('status', ['PUBLISHED', 'published']);
        } else {
            $query->whereIn('status', ['PUBLISHED', 'published', 'DRAFT', 'draft']);
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

        if ($request->filled('q')) {
            SearchLog::record((string) $request->input('q'), (int) $games->total(), $request->ip());
        }

        $consoles = Console::withCount([
            'games as games_count' => fn ($q) => $q->whereIn('status', ['PUBLISHED', 'published']),
            'games as total_uploaded_count' => fn ($q) => $q->whereIn('status', ['PUBLISHED', 'published', 'DRAFT', 'draft']),
        ])->orderBy('name')->get();
        $categories = Category::withCount(['games' => fn ($q) => $q->whereIn('status', ['PUBLISHED', 'published'])])->orderBy('name')->get();

        return view('web.search', compact('games', 'consoles', 'categories'));
    }

    public function live(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $hasPublished = Game::whereIn('status', ['PUBLISHED', 'published'])->exists();
        $allowedStatuses = $hasPublished ? ['PUBLISHED', 'published'] : ['PUBLISHED', 'published', 'DRAFT', 'draft'];

        $results = Game::with('console')
            ->whereIn('status', $allowedStatuses)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('serial', 'like', "%{$q}%")
                    ->orWhere('developer', 'like', "%{$q}%")
                    ->orWhere('publisher', 'like', "%{$q}%")
                    ->orWhereHas('console', fn ($cq) => $cq->where('name', 'like', "%{$q}%"));
            })
            ->take(8)
            ->get()
            ->map(function ($g) {
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
                    'downloads' => number_format((int) $g->download_count),
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
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id));

        if ($request->filled('console')) {
            $query->whereHas('console', fn ($q) => $q->where('slug', $request->input('console')));
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
        $consoles = Console::withCount(['games' => fn ($q) => $q->where('status', 'PUBLISHED')])->orderBy('name')->get();
        $categories = Category::withCount(['games' => fn ($q) => $q->where('status', 'PUBLISHED')])->orderBy('name')->get();
        $activeCategory = $category;

        return view('web.search', compact('games', 'consoles', 'categories', 'activeCategory'));
    }
}
