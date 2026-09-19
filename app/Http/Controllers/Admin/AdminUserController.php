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

    public function profile(): View
    {
        $user = auth()->user();
        return view('admin.profile.index', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'email.unique' => 'El correo electrónico ya está en uso por otro usuario.',
            'username.unique' => 'El nombre de usuario ya está en uso.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'current_password.required_with' => 'Debes ingresar tu contraseña actual para establecer una nueva.',
        ]);

        if ($request->filled('password')) {
            if ($request->filled('current_password') && !\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.'])->withInput();
            }
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('admin.profile')->with('success', '¡Tus credenciales y datos de administrador fueron actualizados correctamente!');
    }
}
