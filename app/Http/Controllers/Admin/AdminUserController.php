<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::withCount('reviews');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:ADMIN,MODERATOR,USER',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', "Rol del usuario '{$user->name}' actualizado a {$user->role}.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activada' : 'suspendida';
        return redirect()->route('admin.users.index')->with('success', "Cuenta de '{$user->name}' {$status}.");
    }
}
