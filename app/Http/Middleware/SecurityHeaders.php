<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Inyecta cabeceras de seguridad HTTP esenciales para mitigar ataques
     * Clickjacking, MIME-Sniffing, XSS e inyección en navegadores modernos.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Previene Clickjacking obligando a que solo se pueda embeber en el mismo dominio
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Previene ataques de confusión de tipo MIME
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Política de referrer segura para proteger URLs internas
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Deshabilita acceso no autorizado a hardware como micrófono o cámara
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
