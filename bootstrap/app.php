<?php

use App\Http\Middleware\HeaderKeamanan;
use App\Http\Middleware\PastikanRental;
use App\Http\Middleware\PeriksaAsal;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rental' => PastikanRental::class,
        ]);
        // Banyak hosting di balik reverse proxy; percayakan header X-Forwarded-*.
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            HeaderKeamanan::class,
            PeriksaAsal::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
