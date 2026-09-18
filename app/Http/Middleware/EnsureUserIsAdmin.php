<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder al panel administrativo.');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Acceso denegado: Se requieren privilegios de Administrador.');
        }

        return $next($request);
    }
}
