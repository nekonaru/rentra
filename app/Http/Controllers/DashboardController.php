<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->with(['units.priceRule'])->firstOrFail();

        $stats = [
            'unitAktif' => $rental->units->where('aktif', true)->count(),
            'unitTotal' => $rental->units->count(),
            'menungguVerifikasi' => $rental->bookings()->where('status', BookingStatus::MenungguVerifikasi)->count(),
            'ditahan' => $rental->bookings()->reserving()->where('status', BookingStatus::Ditahan)->count(),
            'terlambat' => $rental->bookings()->where('status', BookingStatus::Terlambat)->count(),
        ];

        $terlambat = $rental->bookings()
            ->with('unit')
            ->where('status', BookingStatus::Terlambat)
            ->orderBy('selesai')
            ->get();

        return view('dashboard', [
            'rental' => $rental,
            'stats' => $stats,
            'terlambat' => $terlambat,
        ]);
    }
}
