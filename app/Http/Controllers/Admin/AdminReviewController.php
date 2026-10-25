<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::with(['game.console', 'user']);

        // Filter by status if requested
        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        // Search query
        if ($request->filled('q')) {
            $search = trim($request->query('q'));
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%")
                    ->orWhere('tested_emulator', 'like', "%{$search}%")
                    ->orWhereHas('game', function ($g) use ($search) {
                        $g->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Show pending reviews first, then most recent
        $reviews = $query->orderBy('is_approved', 'asc')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('is_approved', false)->count(),
            'approved' => Review::where('is_approved', true)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->is_approved = true;
        $review->save();

        return redirect()->route('admin.reviews.index')->with('success', 'Reseña aprobada y visible públicamente.');
    }

    public function reject(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Reseña rechazada y eliminada.');
    }
}
