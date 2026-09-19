<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('web.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Solo permitir acceso si el usuario es Administrador o Moderador
            if (Auth::user()->isAdmin() || Auth::user()->role === 'MODERATOR') {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Si es usuario regular, desconectar
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'El acceso para usuarios públicos se encuentra temporalmente deshabilitado. Este portal es de uso exclusivo para administración.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function showRegister(): RedirectResponse
    {
        return redirect()->route('login')->with('info', 'El registro de usuarios públicos se encuentra temporalmente deshabilitado.');
    }

    public function register(Request $request): RedirectResponse
    {
        return redirect()->route('login')->with('error', 'El registro de nuevos usuarios se encuentra temporalmente cerrado.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Sesión cerrada correctamente.');
    }
}
