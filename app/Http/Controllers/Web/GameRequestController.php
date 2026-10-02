<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameRequest;
use App\Models\GameRequestVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GameRequestController extends Controller
{
    /**
     * Display a listing of community game requests.
     */
    public function index(Request $request): View
    {
        $ipHash = hash('sha256', $request->ip().'_vault_picho_salt');
        $userId = Auth::id();

        $consoles = Console::where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $query = GameRequest::with(['console', 'completedGame', 'user']);

        // Filter: Search title
        if ($request->filled('q')) {
            $searchTerm = trim($request->input('q'));
            $query->where('title', 'like', "%{$searchTerm}%");
        }

        // Filter: Console
        if ($request->filled('console')) {
            $consoleSlug = $request->input('console');
            $query->whereHas('console', function ($q) use ($consoleSlug) {
                $q->where('slug', $consoleSlug);
            });
        }

        // Filter: Status / Sorting tab
        $tab = $request->input('tab', 'mas-votados');
        match ($tab) {
            'recientes' => $query->latest('id'),
            'en-proceso' => $query->where('status', 'in_progress')->orderByDesc('votes_count'),
            'completados' => $query->where('status', 'completed')->latest('updated_at'),
            'esperando' => $query->where('status', 'pending')->orderByDesc('votes_count'),
            default => $query->whereIn('status', ['pending', 'in_progress'])->orderByDesc('votes_count')->latest('id'),
        };

        $requests = $query->paginate(15)->withQueryString();

        // Get array of request IDs already voted by this IP/user
        $myVotedIds = GameRequestVote::where('ip_hash', $ipHash)
            ->when($userId, fn ($q) => $q->orWhere('user_id', $userId))
            ->pluck('game_request_id')
            ->toArray();

        // Stats summary for the top bar
        $stats = [
            'total' => GameRequest::count(),
            'pending' => GameRequest::where('status', 'pending')->count(),
            'in_progress' => GameRequest::where('status', 'in_progress')->count(),
            'completed' => GameRequest::where('status', 'completed')->count(),
        ];

        return view('web.requests.index', compact('requests', 'consoles', 'tab', 'myVotedIds', 'stats'));
    }

    /**
     * Submit a new game request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|min:2|max:150',
            'console_id' => 'required|exists:consoles,id',
            'region' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'requester_name' => 'nullable|string|max:50',
        ], [
            'title.required' => 'El título del videojuego es obligatorio.',
            'title.min' => 'El título debe tener al menos 2 caracteres.',
            'console_id.required' => 'Debes seleccionar una consola.',
            'console_id.exists' => 'La consola seleccionada no es válida.',
        ]);

        $ipHash = hash('sha256', $request->ip().'_vault_picho_salt');
        $userId = Auth::id();
        $cleanTitle = trim($validated['title']);

        // 1. Check if game already exists in the catalog!
        $existingGame = Game::where('console_id', $validated['console_id'])
            ->whereRaw('LOWER(title) = ?', [strtolower($cleanTitle)])
            ->first();

        if ($existingGame) {
            return redirect()->route('game.show', $existingGame->slug)
                ->with('info', "¡Buenas noticias! '{$existingGame->title}' ya está disponible en nuestro Vault para descargar.");
        }

        // 2. Check if a request for this game & console already exists
        $existingRequest = GameRequest::where('console_id', $validated['console_id'])
            ->whereRaw('LOWER(title) = ?', [strtolower($cleanTitle)])
            ->first();

        if ($existingRequest) {
            // Check if this user already voted
            $alreadyVoted = GameRequestVote::where('game_request_id', $existingRequest->id)
                ->where(function ($q) use ($ipHash, $userId) {
                    $q->where('ip_hash', $ipHash);
                    if ($userId) {
                        $q->orWhere('user_id', $userId);
                    }
                })
                ->exists();

            if (! $alreadyVoted) {
                GameRequestVote::create([
                    'game_request_id' => $existingRequest->id,
                    'user_id' => $userId,
                    'ip_hash' => $ipHash,
                    'created_at' => now(),
                ]);
                $existingRequest->increment('votes_count');

                return redirect()->route('requests.index', ['q' => $cleanTitle])
                    ->with('success', "¡Ese juego ya había sido solicitado! Le hemos sumado tu voto de apoyo (Total: {$existingRequest->votes_count} votos).");
            }

            return redirect()->route('requests.index', ['q' => $cleanTitle])
                ->with('info', 'Esta petición ya existe en la lista y ya la has apoyado.');
        }

        // 3. Create new request
        $newRequest = GameRequest::create([
            'user_id' => $userId,
            'console_id' => $validated['console_id'],
            'title' => $cleanTitle,
            'region' => $validated['region'] ?: 'Universal / Español',
            'notes' => $validated['notes'],
            'votes_count' => 1,
            'status' => 'pending',
            'requester_name' => $validated['requester_name'] ?: (Auth::check() ? Auth::user()->name : 'Coleccionista Retro'),
            'ip_hash' => $ipHash,
        ]);

        // Register initial creator's vote
        GameRequestVote::create([
            'game_request_id' => $newRequest->id,
            'user_id' => $userId,
            'ip_hash' => $ipHash,
            'created_at' => now(),
        ]);

        return redirect()->route('requests.index', ['tab' => 'recientes'])
            ->with('success', "¡Petición enviada exitosamente para '{$cleanTitle}'! La comunidad ya puede votar por ella.");
    }

    /**
     * Upvote or remove vote for a game request.
     */
    public function vote(Request $request, GameRequest $gameRequest): JsonResponse|RedirectResponse
    {
        $ipHash = hash('sha256', $request->ip().'_vault_picho_salt');
        $userId = Auth::id();

        $existingVote = GameRequestVote::where('game_request_id', $gameRequest->id)
            ->where(function ($q) use ($ipHash, $userId) {
                $q->where('ip_hash', $ipHash);
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->first();

        if ($existingVote) {
            // Remove vote
            $existingVote->delete();
            $gameRequest->decrement('votes_count');
            $voted = false;
            $message = 'Has retirado tu voto de esta petición.';
        } else {
            // Add vote
            GameRequestVote::create([
                'game_request_id' => $gameRequest->id,
                'user_id' => $userId,
                'ip_hash' => $ipHash,
                'created_at' => now(),
            ]);
            $gameRequest->increment('votes_count');
            $voted = true;
            $message = '¡Voto registrado! Gracias por apoyar esta ROM.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'voted' => $voted,
                'votes_count' => max(0, $gameRequest->fresh()->votes_count),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
