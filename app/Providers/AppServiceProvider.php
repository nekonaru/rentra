<?php

namespace App\Providers;

use App\Listeners\CatatPeristiwaKeamanan;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan service aplikasi.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap service aplikasi.
     */
    public function boot(): void
    {
        $listener = new CatatPeristiwaKeamanan;

        Event::listen(Login::class, [$listener, 'handleLogin']);
        Event::listen(Logout::class, [$listener, 'handleLogout']);
        Event::listen(Failed::class, [$listener, 'handleGagal']);
        Event::listen(PasswordReset::class, [$listener, 'handlePasswordReset']);
    }
}
