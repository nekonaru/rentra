<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PeriksaAsal
{
    public function handle(Request $request, Closure $next): Response
    {
        $metodeUbah = ['POST', 'PUT', 'PATCH', 'DELETE'];

        if (in_array($request->getMethod(), $metodeUbah, true)) {
            $asal = $request->headers->get('origin');

            if ($asal !== null) {
                $hostAsal = parse_url($asal, PHP_URL_HOST);

                if ($hostAsal === null || strcasecmp($hostAsal, $request->getHost()) !== 0) {
                    abort(403, 'Permintaan lintas situs ditolak.');
                }
            }
        }

        return $next($request);
    }
}
