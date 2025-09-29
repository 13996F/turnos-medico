<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        // CSP: permitir recursos locales y CDNs usados (Bootstrap, Bootstrap Icons, Fonts Bunny)
        $csp = "default-src 'self'; "
            . "script-src 'self' https://cdn.jsdelivr.net; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; "
            . "font-src 'self' https://cdn.jsdelivr.net https://fonts.bunny.net data:; "
            . "img-src 'self' data:; "
            . "connect-src 'self'; "
            . "frame-ancestors 'self'";
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS solo en producción y con HTTPS
        if (app()->environment('production') && $request->isSecure()) {
            // 6 meses, incluir subdominios
            $response->headers->set('Strict-Transport-Security', 'max-age=15552000; includeSubDomains');
        }

        return $response;
    }
}

