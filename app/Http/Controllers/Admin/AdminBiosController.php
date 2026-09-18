<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bios;
use App\Models\Console;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminBiosController extends Controller
{
    public function index(): View
    {
        $biosList = Bios::with('console')->orderBy('order')->orderBy('id')->get();
        $consoles = Console::orderBy('name')->get();

        return view('admin.bios.index', compact('biosList', 'consoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'system' => 'required|string|max:255',
            'console_id' => 'nullable|exists:consoles,id',
            'console_slug' => 'nullable|string|max:100',
            'files' => 'required|string|max:255',
            'version' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:50',
            'format' => 'nullable|string|max:50',
            'md5' => 'nullable|string|max:64',
            'sha1' => 'nullable|string|max:64',
            'emulator' => 'nullable|string|max:255',
            'download_url' => 'required|url|max:1000',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        if (!empty($validated['console_id'])) {
            $console = Console::find($validated['console_id']);
            $validated['console_slug'] = $console?->slug;
        }

        Bios::create($validated);

        return redirect()->route('admin.bios.index')->with('success', 'BIOS agregada exitosamente al catálogo.');
    }

    public function update(Request $request, Bios $bio): RedirectResponse
    {
        $validated = $request->validate([
            'system' => 'required|string|max:255',
            'console_id' => 'nullable|exists:consoles,id',
            'console_slug' => 'nullable|string|max:100',
            'files' => 'required|string|max:255',
            'version' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:50',
            'format' => 'nullable|string|max:50',
            'md5' => 'nullable|string|max:64',
            'sha1' => 'nullable|string|max:64',
            'emulator' => 'nullable|string|max:255',
            'download_url' => 'required|url|max:1000',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        if (!empty($validated['console_id'])) {
            $console = Console::find($validated['console_id']);
            $validated['console_slug'] = $console?->slug;
        }

        $bio->update($validated);

        return redirect()->route('admin.bios.index')->with('success', 'BIOS actualizada correctamente.');
    }

    public function destroy(Bios $bio): RedirectResponse
    {
        $bio->delete();
        return redirect()->route('admin.bios.index')->with('success', 'Registro de BIOS eliminado.');
    }
}
