<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emulator;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminEmulatorController extends Controller
{
    public function index(): View
    {
        $emulators = Emulator::orderBy('order')->orderBy('id')->get();
        return view('admin.emulators.index', compact('emulators'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'system' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'platforms' => 'nullable|array',
            'version' => 'nullable|string|max:100',
            'features' => 'nullable|string',
            'license' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:1000',
            'download_url' => 'required|url|max:1000',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['icon'] = $validated['icon'] ?: 'cpu';
        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        if (!empty($validated['features'])) {
            $validated['features'] = array_values(array_filter(array_map('trim', explode(',', $validated['features']))));
        } else {
            $validated['features'] = [];
        }

        Emulator::create($validated);

        return redirect()->route('admin.emulators.index')->with('success', 'Emulador agregado exitosamente.');
    }

    public function update(Request $request, Emulator $emulator): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'system' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'platforms' => 'nullable|array',
            'version' => 'nullable|string|max:100',
            'features' => 'nullable|string',
            'license' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:1000',
            'download_url' => 'required|url|max:1000',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['icon'] = $validated['icon'] ?: 'cpu';
        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter(array_map('trim', explode(',', $validated['features']))));
        }

        $emulator->update($validated);

        return redirect()->route('admin.emulators.index')->with('success', 'Emulador actualizado correctamente.');
    }

    public function destroy(Emulator $emulator): RedirectResponse
    {
        $emulator->delete();
        return redirect()->route('admin.emulators.index')->with('success', 'Emulador eliminado del catálogo.');
    }
}
