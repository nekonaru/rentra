<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Catat aktivitas pemilik untuk keperluan riwayat.
     */
    protected function catatAktivitas(int $rentalId, string $aktivitas, ?string $keterangan = null): void
    {
        LogAktivitas::create([
            'rental_id' => $rentalId,
            'user_id' => auth()->id(),
            'aktivitas' => $aktivitas,
            'keterangan' => $keterangan,
        ]);
    }
}
