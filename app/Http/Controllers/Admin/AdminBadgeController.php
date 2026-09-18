<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminBadgeController extends Controller
{
    public function index(): View
    {
        $badges = Badge::withCount('games')->orderBy('name')->get();
        return view('admin.badges.index', compact('badges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:badges,slug',
            'text_color' => 'nullable|string|max:40',
            'bg_color' => 'nullable|string|max:40',
            'border_color' => 'nullable|string|max:40',
            'icon' => 'nullable|string|max:40',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Badge::create($validated);

        return redirect()->route('admin.badges.index')->with('success', 'Badge creado con éxito.');
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:badges,slug,' . $badge->id,
            'text_color' => 'nullable|string|max:40',
            'bg_color' => 'nullable|string|max:40',
            'border_color' => 'nullable|string|max:40',
            'icon' => 'nullable|string|max:40',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = $badge->slug ?: Str::slug($validated['name']);
        }

        $badge->update($validated);

        return redirect()->route('admin.badges.index')->with('success', "Insignia '{$badge->name}' actualizada con éxito.");
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $name = $badge->name;
        $badge->delete();

        return redirect()->route('admin.badges.index')->with('success', "Badge '{$name}' eliminado.");
    }
}
