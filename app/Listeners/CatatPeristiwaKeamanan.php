<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;

class CatatPeristiwaKeamanan
{
    public function handleLogin(Login $event): void
    {
        Log::channel('keamanan')->info('login', $this->konteks([
            'user_id' => $event->user->getAuthIdentifier(),
            'email' => $event->user->email ?? null,
            'guard' => $event->guard,
        ]));
    }

    public function handleLogout(Logout $event): void
    {
        Log::channel('keamanan')->info('logout', $this->konteks([
            'user_id' => $event->user?->getAuthIdentifier(),
            'guard' => $event->guard,
        ]));
    }

    public function handleGagal(Failed $event): void
    {
        // Jangan pernah mencatat password dari kredensial.
        Log::channel('keamanan')->warning('login_gagal', $this->konteks([
            'email' => $event->credentials['email'] ?? null,
            'guard' => $event->guard,
        ]));
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        Log::channel('keamanan')->info('password_direset', $this->konteks([
            'user_id' => $event->user->getAuthIdentifier(),
            'email' => $event->user->email ?? null,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function konteks(array $data): array
    {
        return array_merge($data, [
            'ip' => request()->ip(),
            'agen' => substr((string) request()->header('User-Agent'), 0, 200),
            'url' => request()->path(),
        ]);
    }
}
