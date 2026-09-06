<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité HTTP appliqués à toutes les réponses.
 *
 * Note sur la CSP : Alpine.js (utilisé sur tout le site public et
 * l'administration) compile ses expressions de directives via
 * `new Function()`, ce qui nécessite 'unsafe-eval' — et plusieurs vues
 * admin utilisent encore des attributs onsubmit="" (confirmations de
 * suppression), ce qui nécessite 'unsafe-inline' pour script-src. Verrouiller
 * davantage (nonces, build CSP d'Alpine) est un chantier à part, à mener
 * sans risquer de casser le site public/l'admin validés — voir docs/DEPLOYMENT.md.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        return $response;
    }

    protected function contentSecurityPolicy(): string
    {
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval'";
        $connectSrc = "'self'";

        if (app()->environment('local')) {
            // Serveur de dev Vite (npm run dev) et sa liaison HMR websocket.
            $scriptSrc .= ' http://localhost:5173';
            $connectSrc .= ' http://localhost:5173 ws://localhost:5173';
        }

        $directives = [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data:",
            "connect-src {$connectSrc}",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        return implode('; ', $directives);
    }
}
