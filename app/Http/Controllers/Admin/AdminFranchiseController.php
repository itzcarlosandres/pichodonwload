<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class AdminFranchiseController extends Controller
{
    public function index(): View
    {
        $franchises = Franchise::withCount('games')->orderBy('order')->orderBy('id')->get();
        $games = Game::select('id', 'title', 'console_id')->with('console:id,name')->orderBy('title')->get();

        return view('admin.franchises.index', compact('franchises', 'games'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:franchises,slug',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|url|max:1000',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'search_terms' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
            'game_ids' => 'nullable|array',
            'game_ids.*' => 'exists:games,id',
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['icon'] = $validated['icon'] ?: 'sparkles';
        $validated['color'] = $validated['color'] ?: '#CE2D2D';
        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        if (!empty($validated['search_terms'])) {
            $validated['search_terms'] = array_values(array_filter(array_map('trim', explode(',', $validated['search_terms']))));
        } else {
            $validated['search_terms'] = [$validated['name']];
        }

        $franchise = Franchise::create($validated);

        if (!empty($validated['game_ids'])) {
            $franchise->games()->sync($validated['game_ids']);
        }

        return redirect()->route('admin.franchises.index')->with('success', 'Saga / Franquicia creada exitosamente.');
    }

    public function update(Request $request, Franchise $franchise): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:franchises,slug,' . $franchise->id,
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|url|max:1000',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'search_terms' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
            'game_ids' => 'nullable|array',
            'game_ids.*' => 'exists:games,id',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['icon'] = $validated['icon'] ?: 'sparkles';
        $validated['color'] = $validated['color'] ?: '#CE2D2D';
        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        if (isset($validated['search_terms'])) {
            $validated['search_terms'] = array_values(array_filter(array_map('trim', explode(',', $validated['search_terms']))));
        }

        $franchise->update($validated);

        if (isset($validated['game_ids'])) {
            $franchise->games()->sync($validated['game_ids']);
        } else {
            $franchise->games()->detach();
        }

        return redirect()->route('admin.franchises.index')->with('success', 'Saga / Franquicia actualizada correctamente.');
    }

    public function destroy(Franchise $franchise): RedirectResponse
    {
        $franchise->games()->detach();
        $franchise->delete();

        return redirect()->route('admin.franchises.index')->with('success', 'Saga eliminada del catálogo.');
    }

    public function toggleStatus(Franchise $franchise): RedirectResponse
    {
        $franchise->update(['is_active' => !$franchise->is_active]);
        $statusText = $franchise->is_active ? 'publicada' : 'ocultada';

        return redirect()->route('admin.franchises.index')->with('success', "La saga '{$franchise->name}' ahora está {$statusText}.");
    }
}
