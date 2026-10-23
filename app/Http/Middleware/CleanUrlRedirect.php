<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanUrlRedirect
{
    /**
     * Elimina /index.php/ de las URLs entrantes mediante redirección 301 Permanente
     * para unificar la versión canónica ante Google Search Console y evitar duplicados.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uri = $request->getRequestUri();

        // Si la petición contiene /index.php o /index.php/
        if (str_contains($uri, '/index.php')) {
            $path = ltrim($request->path(), '/');
            $cleanUri = '/'.$path;

            $queryString = $request->getQueryString();
            if ($queryString) {
                $cleanUri .= '?'.$queryString;
            }

            return redirect($cleanUri, 301);
        }

        $response = $next($request);

        // Si es una ruta de descarga de BIOS o juego, añadir cabecera HTTP X-Robots-Tag
        if ($request->is('vault/download/*') || $request->is('game/*/download') || $request->is('download/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
