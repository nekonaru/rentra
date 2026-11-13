<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Requests\TolakBuktiRequest;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        $menunggu = $rental->bookings()
            ->with('unit')
            ->where('status', BookingStatus::MenungguVerifikasi)
            ->orderBy('created_at')
            ->get();

        $ditahan = $rental->bookings()
            ->with('unit')
            ->where('status', BookingStatus::Ditahan)
            ->where('hold_expires_at', '>', now())
            ->orderBy('hold_expires_at')
            ->get();

        return view('dashboard.verifikasi', [
            'menunggu' => $menunggu,
            'ditahan' => $ditahan,
        ]);
    }

    public function terima(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('terima', $booking);

        $booking->update([
            'status' => BookingStatus::Terkonfirmasi,
            'hold_expires_at' => null,
            'catatan_tolak' => null,
        ]);

        return back()->with('status', 'Booking '.$booking->kode.' dikonfirmasi.');
    }

    public function tolak(TolakBuktiRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('tolak', $booking);

        $rental = $request->user()->rental;

        $booking->update([
            'status' => BookingStatus::Ditahan,
            'hold_expires_at' => now()->addMinutes($rental->durasi_hold_menit),
            'catatan_tolak' => $request->validated('catatan_tolak'),
        ]);

        return back()->with('status', 'Bukti ditolak. Penyewa bisa upload ulang dalam '.$rental->durasi_hold_menit.' menit.');
    }
}
