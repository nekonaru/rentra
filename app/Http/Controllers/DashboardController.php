<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Models\Pengumuman;
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
            'omzetBulanIni' => (float) $rental->pemesanan()
                ->whereIn('status', [StatusPemesanan::Selesai->value, StatusPemesanan::SedangDisewa->value, StatusPemesanan::Terlambat->value])
                ->whereMonth('selesai', now()->month)
                ->whereYear('selesai', now()->year)
                ->sum('total'),
            'jumlahBulanIni' => $rental->pemesanan()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $agenda = $rental->pemesanan()
            ->with('unit')
            ->whereIn('status', [StatusPemesanan::Terkonfirmasi->value, StatusPemesanan::SedangDisewa->value, StatusPemesanan::Terlambat->value])
            ->where('mulai', '<=', now()->endOfDay())
            ->where('selesai', '>=', now()->startOfDay())
            ->orderBy('mulai')
            ->get();

        $terlambat = $rental->pemesanan()
            ->with('unit')
            ->where('status', StatusPemesanan::Terlambat)
            ->orderBy('selesai')
            ->get();

        return view('dashboard', [
            'rental' => $rental,
            'statistik' => $statistik,
            'terlambat' => $terlambat,
            'agenda' => $agenda,
            'pengumuman' => Pengumuman::aktif()->first(),
        ]);
    }
}
