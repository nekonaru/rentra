<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->with(['unit.aturanTarif'])->firstOrFail();

        $statistik = [
            'unitAktif' => $rental->unit->where('aktif', true)->count(),
            'unitTotal' => $rental->unit->count(),
            'menungguVerifikasi' => $rental->pemesanan()->where('status', StatusPemesanan::MenungguVerifikasi)->count(),
            'ditahan' => $rental->pemesanan()->menahanSlot()->where('status', StatusPemesanan::Ditahan)->count(),
            'terlambat' => $rental->pemesanan()->where('status', StatusPemesanan::Terlambat)->count(),
        ];

        $terlambat = $rental->pemesanan()
            ->with('unit')
            ->where('status', StatusPemesanan::Terlambat)
            ->orderBy('selesai')
            ->get();

        return view('dashboard', [
            'rental' => $rental,
            'statistik' => $statistik,
            'terlambat' => $terlambat,
        ]);
    }
}
