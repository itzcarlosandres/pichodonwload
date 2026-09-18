<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Setting;
use App\Models\Bios;
use App\Models\Emulator;
use App\Models\Franchise;
use App\Models\Game;
use App\Models\Console;

class PageController extends Controller
{
    /**
     * Muestra la página de Política DMCA y Retiro de Contenido
     */
    public function dmca(): View
    {
        $contactEmail = Setting::get('contact_email', 'dmca@romhub.io');
        return view('web.dmca', compact('contactEmail'));
    }

    /**
     * Muestra la página de Contacto & Soporte
     */
    public function contact(): View
    {
        $contactEmail = Setting::get('contact_email', 'contacto@romhub.io');
        return view('web.contact', compact('contactEmail'));
    }

    /**
     * Procesa el formulario de contacto
     */
    public function submitContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|min:10|max:2000',
        ]);

        return back()->with('success', '¡Tu mensaje ha sido enviado exitosamente! Nos pondremos en contacto contigo a la brevedad.');
    }

    /**
     * Módulo 3: Directorio de BIOS Verificadas desde Base de Datos
     */
    public function bios(): View
    {
        $biosList = Bios::where('is_active', true)
            ->with('console')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return view('web.bios', compact('biosList'));
    }

    /**
     * Módulo 3: Directorio de Emuladores Oficiales desde Base de Datos
     */
    public function emulators(): View
    {
        $emulators = Emulator::where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return view('web.emulators', compact('emulators'));
    }

    /**
     * Sistema 2: Hub de Sagas y Franquicias desde Base de Datos
     */
    public function collections(): View
    {
        $franchises = Franchise::where('is_active', true)
            ->withCount('games')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return view('web.collections-index', compact('franchises'));
    }

    /**
     * Sistema 2: Detalle de Colección / Saga desde Base de Datos
     */
    public function collectionDetail(string $slug): View
    {
        $franchise = Franchise::with('games')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $manualIds = $franchise->games->pluck('id')->toArray();
        $searchTerms = is_array($franchise->search_terms) ? $franchise->search_terms : [$franchise->name];

        $games = Game::with(['console', 'badges'])
            ->where('status', 'PUBLISHED')
            ->where(function($q) use ($manualIds, $searchTerms) {
                if (!empty($manualIds)) {
                    $q->whereIn('id', $manualIds);
                }
                if (!empty($searchTerms)) {
                    $q->orWhere(function($sub) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            if (!empty(trim($term))) {
                                $sub->orWhere('title', 'like', '%' . trim($term) . '%')
                                    ->orWhere('developer', 'like', '%' . trim($term) . '%');
                            }
                        }
                    });
                }
            })
            ->when(request('console'), fn($q, $c) => $q->whereHas('console', fn($sq) => $sq->where('slug', $c)))
            ->orderByDesc('download_count')
            ->paginate(24)
            ->withQueryString();

        return view('web.collection-detail', compact('franchise', 'games', 'slug'));
    }

    /**
     * Módulo 3: Rankings "Top 25 Más Jugados / Legendarios" por Consola
     */
    public function rankings(Request $request): View
    {
        $selectedConsole = $request->input('console');
        $sortBy = $request->input('sort', 'downloads'); // 'downloads', 'rating', 'views'

        $consoles = Console::whereHas('games', fn($q) => $q->where('status', 'PUBLISHED'))
            ->withCount(['games' => fn($q) => $q->where('status', 'PUBLISHED')])
            ->orderByDesc('games_count')
            ->get();

        $activeConsole = null;
        if ($selectedConsole) {
            $activeConsole = Console::where('slug', $selectedConsole)->first();
        }

        $query = Game::with(['console', 'badges', 'categories'])
            ->where('status', 'PUBLISHED');

        if ($activeConsole) {
            $query->where('console_id', $activeConsole->id);
        }

        if ($sortBy === 'rating') {
            $query->orderByDesc('rating_average')->orderByDesc('rating_count');
        } elseif ($sortBy === 'views') {
            $query->orderByDesc('views_count');
        } else {
            $query->orderByDesc('download_count')->orderByDesc('views_count');
        }

        $topGames = $query->take(25)->get();

        return view('web.rankings', compact('topGames', 'consoles', 'activeConsole', 'selectedConsole', 'sortBy'));
    }
}
