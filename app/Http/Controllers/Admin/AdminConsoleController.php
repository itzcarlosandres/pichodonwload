<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Console;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminConsoleController extends Controller
{
    public function index(): View
    {
        $consoles = Console::withCount('games')->orderBy('order')->get();
        return view('admin.consoles.index', compact('consoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('generation')) {
            $genClean = intval(preg_replace('/[^0-9]/', '', (string)$request->input('generation')));
            $request->merge(['generation' => $genClean > 0 ? $genClean : null]);
        }
        if ($request->input('manufacturer') === 'Otro') {
            $request->merge(['manufacturer' => 'Other']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:consoles,slug',
            'short_name' => 'nullable|string|max:20',
            'manufacturer' => 'required|in:Sony,Nintendo,Microsoft,Sega,Arcade,Other',
            'generation' => 'nullable|integer',
            'release_year' => 'nullable|integer',
            'recommended_emulator' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_featured' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['order'] = $validated['order'] ?? ((int) Console::max('order') + 1);

        Console::create($validated);

        return redirect()->route('admin.consoles.index')->with('success', "Ecosistema / Consola '{$validated['name']}' registrado exitosamente.");
    }

    public function update(Request $request, Console $console): RedirectResponse
    {
        if ($request->filled('generation')) {
            $genClean = intval(preg_replace('/[^0-9]/', '', (string)$request->input('generation')));
            $request->merge(['generation' => $genClean > 0 ? $genClean : null]);
        }
        if ($request->input('manufacturer') === 'Otro') {
            $request->merge(['manufacturer' => 'Other']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:consoles,slug,' . $console->id,
            'short_name' => 'nullable|string|max:20',
            'manufacturer' => 'required|in:Sony,Nintendo,Microsoft,Sega,Arcade,Other',
            'generation' => 'nullable|integer',
            'release_year' => 'nullable|integer',
            'recommended_emulator' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_featured' => 'boolean',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        $console->update($validated);

        return redirect()->route('admin.consoles.index')->with('success', "Consola '{$console->name}' actualizada correctamente.");
    }

    public function destroy(Console $console): RedirectResponse
    {
        $name = $console->name;
        $console->delete();

        return redirect()->route('admin.consoles.index')->with('success', "Consola '{$name}' eliminada.");
    }
}
