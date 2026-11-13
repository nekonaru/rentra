<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class KalenderController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->with('unit')->firstOrFail();

        $bulan = $this->uraiBulan($request->query('bulan'));
        $hari = $this->uraiHari($request->query('hari'), $bulan);

        // Ambil semua pemesanan aktif yang menyentuh bulan ini.
        $pemesanan = $rental->pemesanan()
            ->with('unit')
            ->whereIn('status', [
                StatusPemesanan::Ditahan->value,
                StatusPemesanan::MenungguVerifikasi->value,
                StatusPemesanan::Terkonfirmasi->value,
                StatusPemesanan::SedangDisewa->value,
                StatusPemesanan::Terlambat->value,
            ])
            ->where('mulai', '<', $bulan->copy()->endOfMonth()->endOfDay())
            ->where('selesai', '>', $bulan->copy()->startOfMonth()->startOfDay())
            ->orderBy('mulai')
            ->get();

        // Kelompokkan pemesanan per hari (setiap hari dalam rentang [mulai, selesai-1 menit]).
        $perHari = [];
        foreach ($pemesanan as $p) {
            $cursor = $p->mulai->copy()->startOfDay();
            $akhir = $p->selesai->copy()->subMinute()->startOfDay();
            while ($cursor->lte($akhir)) {
                $key = $cursor->toDateString();
                $perHari[$key] ??= [];
                $perHari[$key][] = $p;
                $cursor->addDay();
            }
        }

        // Pemesanan untuk hari terpilih (lengkap termasuk selesai/dibatalkan).
        $detail = $rental->pemesanan()
            ->with('unit')
            ->where('mulai', '<', $hari->copy()->endOfDay())
            ->where('selesai', '>', $hari->copy()->startOfDay())
            ->orderBy('mulai')
            ->get();

        return view('dashboard.kalender', [
            'rental' => $rental,
            'bulan' => $bulan,
            'hari' => $hari,
            'perHari' => $perHari,
            'detail' => $detail,
            'unit' => $rental->unit,
        ]);
    }

    private function uraiBulan(?string $bulan): Carbon
    {
        try {
            $parsed = $bulan !== null ? Carbon::createFromFormat('Y-m', $bulan) : Carbon::now();
        } catch (\Throwable) {
            $parsed = false;
        }

        if (! $parsed instanceof Carbon) {
            $parsed = Carbon::now();
        }

        return $parsed->startOfMonth();
    }

    private function uraiHari(?string $hari, Carbon $fallback): Carbon
    {
        try {
            $parsed = $hari !== null ? Carbon::createFromFormat('Y-m-d', $hari) : $fallback->copy();
        } catch (\Throwable) {
            $parsed = false;
        }

        if (! $parsed instanceof Carbon) {
            $parsed = $fallback->copy();
        }

        return $parsed->startOfDay();
    }
}
