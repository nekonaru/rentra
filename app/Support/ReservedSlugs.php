<?php

namespace App\Support;

class ReservedSlugs
{
    /**
     * Slugs that collide with application routes and must not be used as
     * public rental slugs.
     *
     * @return array<int, string>
     */
    public static function list(): array
    {
        return [
            'dashboard', 'units', 'login', 'register', 'logout', 'onboarding',
            'profile', 'booking', 'storage', 'build', 'api', 'password',
            'forgot-password', 'reset-password', 'verify-email', 'confirm-password',
            'up', 'sanctum', 'email', '_ignition',
        ];
    }
}
