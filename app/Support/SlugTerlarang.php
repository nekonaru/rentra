<?php

namespace App\Support;

class SlugTerlarang
{
    /**
     * Slug yang bertabrakan dengan route aplikasi, tidak boleh dipakai
     * sebagai slug rental publik.
     *
     * @return array<int, string>
     */
    public static function daftar(): array
    {
        return [
            'dashboard', 'unit', 'login', 'register', 'logout', 'onboarding',
            'profile', 'pemesanan', 'storage', 'build', 'api', 'password',
            'forgot-password', 'reset-password', 'verify-email', 'confirm-password',
            'pengaturan', 'verifikasi', 'up', 'sanctum', 'email', '_ignition',
            'admin', 'laporan', 'kalender',
        ];
    }
}
