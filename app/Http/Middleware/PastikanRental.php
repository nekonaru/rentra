<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanRental
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isAdmin()) {
            // Admin tidak punya area pemilik; kirim ke panel admin.
            return redirect()->route('admin.index');
        }

        if ($user !== null && ! $user->rental()->exists()) {
            return redirect()->route('onboarding.buat');
        }

        return $next($request);
    }
}
