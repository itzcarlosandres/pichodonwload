<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminReviewController extends Controller
{
    public function index(): View
    {
        $pendingReviews = Review::with(['game', 'user'])->where('is_approved', false)->latest()->get();
        $approvedReviews = Review::with(['game', 'user'])->where('is_approved', true)->latest()->paginate(15);

        return view('admin.reviews.index', compact('pendingReviews', 'approvedReviews'));
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
