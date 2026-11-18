<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Redirect to HTTPS when required and add standard security headers.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $forceHttps = (bool) config('lekobosi.security.force_https');

        if ($forceHttps && ! $request->secure()) {
            return redirect()->secure($request->getRequestUri(), $request->isMethod('GET') ? 301 : 308);
        }

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($forceHttps && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age='.(int) config('lekobosi.security.hsts_max_age').'; includeSubDomains');
        }

        return $response;
    }
}
