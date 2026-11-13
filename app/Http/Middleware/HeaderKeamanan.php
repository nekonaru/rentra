<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HeaderKeamanan
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && ! $request->secure() && $request->path() !== 'up') {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        $response = $next($request);

        $isProduction = app()->environment('production');

        $csp = "default-src 'self'; "
            ."script-src 'self' 'unsafe-eval'; " // Alpine.js mengevaluasi ekspresi x-bind/x-on
            ."style-src 'self' 'unsafe-inline' https://fonts.bunny.net; " // style atribut dipakai untuk badge status
            ."font-src 'self' https://fonts.bunny.net; "
            ."img-src 'self' data: blob:; "
            ."connect-src 'self'; "
            ."object-src 'none'; "
            ."base-uri 'self'; "
            ."frame-ancestors 'none'; "
            ."form-action 'self'"
            .($isProduction ? '; upgrade-insecure-requests' : '');

        $headers = [
            'Content-Security-Policy' => $csp,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        if ($isProduction) {
            $headers['Strict-Transport-Security'] = 'max-age=63072000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        // Halaman yang berisi data pribadi tidak di-cache proxy/browser.
        if ($request->user() !== null && ! $request->expectsJson()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        return $response;
    }
}
