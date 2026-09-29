<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyStat;
use App\Models\Game;
use App\Services\RomDownloadResolverService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DownloadController extends Controller
{
    protected RomDownloadResolverService $resolver;

    public function __construct(RomDownloadResolverService $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Muestra la página dedicada y enriquecida de descarga del videojuego
     */
    public function show(string $slug): View
    {
        $query = Game::with(['console', 'categories', 'badges'])->where('slug', $slug);

        if (!auth()->check() || !auth()->user()->isAdmin()) {
            $query->where('status', 'PUBLISHED');
        }

        $game = $query->firstOrFail();

        // Increment views count on download page
        $game->increment('views_count');
        DailyStat::recordView();

        // Obtener juegos relacionados de la misma consola
        $relatedGames = Game::with('console')
            ->where('console_id', $game->console_id)
            ->where('id', '!=', $game->id)
            ->where('status', 'PUBLISHED')
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('web.download', compact('game', 'relatedGames'));
    }

    /**
     * Registra e incrementa el contador de descargas vía AJAX
     */
    public function track(string $slug): JsonResponse
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $game->increment('download_count');
        DailyStat::recordDownload((int) $game->file_size_bytes, 'game', $game->id, $game->title, request()->ip());

        return response()->json([
            'success' => true,
            'download_count' => $game->download_count,
        ]);
    }

    /**
     * Resuelve el enlace de descarga dinámico (actualizando tokens temporales si procede)
     * e incrementa el contador de descargas
     */
    public function resolve(Request $request, string $slug): JsonResponse
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $targetUrl = $request->input('url', $game->download_url);

        if (empty($targetUrl)) {
            $targetUrl = $game->download_url;
        }

        // Resolver URL fresca si es CDRomance u otro enlace con expiración
        $resolvedUrl = $this->resolver->resolve($targetUrl);

        // Incrementar contador de descargas
        $game->increment('download_count');
        DailyStat::recordDownload((int) $game->file_size_bytes, 'game', $game->id, $game->title, $request->ip());

        return response()->json([
            'success' => true,
            'url' => $resolvedUrl,
            'download_count' => $game->download_count,
        ]);
    }

    /**
     * Redirección directa hacia el archivo con resolución transparente
     */
    public function go(Request $request, string $slug): RedirectResponse
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $targetUrl = $request->input('url', $game->download_url);

        if (empty($targetUrl)) {
            $targetUrl = $game->download_url;
        }

        $resolvedUrl = $this->resolver->resolve($targetUrl);
        $game->increment('download_count');
        DailyStat::recordDownload((int) $game->file_size_bytes, 'game', $game->id, $game->title, $request->ip());

        return redirect()->away($resolvedUrl);
    }
}
