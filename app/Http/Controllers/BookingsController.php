<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingsController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        $aktif = $rental->bookings()
            ->with('unit')
            ->whereIn('status', [
                BookingStatus::Terkonfirmasi->value,
                BookingStatus::SedangDisewa->value,
                BookingStatus::Terlambat->value,
            ])
            ->orderBy('mulai')
            ->get();

        $riwayat = $rental->bookings()
            ->with('unit')
            ->whereIn('status', [
                BookingStatus::Selesai->value,
                BookingStatus::Kedaluwarsa->value,
                BookingStatus::Dibatalkan->value,
            ])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('dashboard.bookings', [
            'aktif' => $aktif,
            'riwayat' => $riwayat,
        ]);
    }

    public function serahTerima(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('kelola', $booking);

        abort_unless($booking->status === BookingStatus::Terkonfirmasi, 422);

        $booking->update(['status' => BookingStatus::SedangDisewa]);

        return back()->with('status', 'Booking '.$booking->kode.' ditandai sedang disewa.');
    }

    public function selesai(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('kelola', $booking);

        abort_unless(in_array($booking->status, [BookingStatus::SedangDisewa, BookingStatus::Terlambat], true), 422);

        $booking->update(['status' => BookingStatus::Selesai]);

        return back()->with('status', 'Booking '.$booking->kode.' ditandai selesai.');
    }

    public function batal(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('kelola', $booking);

        abort_unless(in_array($booking->status, [BookingStatus::Ditahan, BookingStatus::MenungguVerifikasi, BookingStatus::Terkonfirmasi], true), 422);

        $booking->update(['status' => BookingStatus::Dibatalkan, 'hold_expires_at' => null]);

        return back()->with('status', 'Booking '.$booking->kode.' dibatalkan.');
    }
}
