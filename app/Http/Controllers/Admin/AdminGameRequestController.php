<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGameRequestController extends Controller
{
    /**
     * Display a listing of game requests for moderation.
     */
    public function index(Request $request): View
    {
        $consoles = Console::orderBy('name')->get();

        $query = GameRequest::with(['console', 'completedGame', 'user']);

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter: Console
        if ($request->filled('console_id')) {
            $query->where('console_id', $request->input('console_id'));
        }

        // Filter: Search
        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'votes');
        if ($sort === 'latest') {
            $query->latest('id');
        } elseif ($sort === 'oldest') {
            $query->oldest('id');
        } else {
            $query->orderByDesc('votes_count')->latest('id');
        }

        $requests = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => GameRequest::count(),
            'pending' => GameRequest::where('status', 'pending')->count(),
            'in_progress' => GameRequest::where('status', 'in_progress')->count(),
            'completed' => GameRequest::where('status', 'completed')->count(),
            'rejected' => GameRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.requests.index', compact('requests', 'consoles', 'stats'));
    }

    /**
     * Update the status and/or link a published game to the request.
     */
    public function updateStatus(Request $request, GameRequest $gameRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,rejected',
            'completed_game_id' => 'nullable|exists:games,id',
        ]);

        $gameRequest->status = $validated['status'];

        if ($validated['status'] === 'completed') {
            $gameRequest->completed_game_id = $validated['completed_game_id'] ?? null;
        } elseif ($validated['status'] !== 'completed') {
            $gameRequest->completed_game_id = null;
        }

        $gameRequest->save();

        return redirect()->back()->with('success', "Estado de la petición '{$gameRequest->title}' actualizado a '{$gameRequest->status}'.");
    }

    /**
     * Remove the game request from the system.
     */
    public function destroy(GameRequest $gameRequest): RedirectResponse
    {
        $title = $gameRequest->title;
        $gameRequest->delete();

        return redirect()->back()->with('success', "Petición '{$title}' eliminada exitosamente.");
    }
}
