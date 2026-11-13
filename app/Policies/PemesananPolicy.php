<?php

namespace App\Policies;

use App\Enums\StatusPemesanan;
use App\Models\Pemesanan;
use App\Models\User;

class PemesananPolicy
{
    public function lihatBukti(User $user, Pemesanan $pemesanan): bool
    {
        return $user->rental?->id === $pemesanan->rental_id;
    }

    public function terima(User $user, Pemesanan $pemesanan): bool
    {
        return $user->rental?->id === $pemesanan->rental_id
            && $pemesanan->status === StatusPemesanan::MenungguVerifikasi;
    }

    public function tolak(User $user, Pemesanan $pemesanan): bool
    {
        return $user->rental?->id === $pemesanan->rental_id
            && $pemesanan->status === StatusPemesanan::MenungguVerifikasi;
    }

    public function kelola(User $user, Pemesanan $pemesanan): bool
    {
        return $user->rental?->id === $pemesanan->rental_id;
    }
}
