<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Exceptions\SlotBentrokException;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UploadBuktiRequest;
use App\Models\Booking;
use App\Models\Rental;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PublicBookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function store(StoreBookingRequest $request, string $slug, int $unit): RedirectResponse
    {
        $rental = Rental::where('slug', $slug)->firstOrFail();
        $unitModel = $rental->units()->where('aktif', true)->findOrFail($unit);

        try {
            $booking = $this->bookingService->buat($rental, $unitModel, $request->validated());
        } catch (SlotBentrokException $e) {
            return back()->withInput()->withErrors(['mulai' => $e->getMessage()]);
        }

        return redirect()->route('public.booking', $booking->kode)
            ->with('status', 'Unit berhasil ditahan. Selesaikan DP sebelum waktu habis.');
    }

    public function show(string $kode): View
    {
        $booking = Booking::where('kode', $kode)->with(['unit.rental'])->firstOrFail();

        // Pengaman: hold yang sudah lewat dianggap kedaluwarsa walau scheduler belum jalan.
        if ($booking->status === BookingStatus::Ditahan
            && $booking->hold_expires_at !== null
            && $booking->hold_expires_at->isPast()) {
            $booking->update(['status' => BookingStatus::Kedaluwarsa]);
            $booking->refresh();
        }

        return view('public.booking', ['booking' => $booking]);
    }

    public function uploadBukti(UploadBuktiRequest $request, string $kode): RedirectResponse
    {
        $booking = Booking::where('kode', $kode)->firstOrFail();

        $bolehUpload = in_array($booking->status, [BookingStatus::Ditahan, BookingStatus::MenungguVerifikasi], true);
        if (! $bolehUpload) {
            return redirect()->route('public.booking', $kode)
                ->withErrors(['bukti' => 'Booking ini tidak bisa menerima bukti DP lagi.']);
        }

        if ($booking->status === BookingStatus::Ditahan
            && $booking->hold_expires_at !== null
            && $booking->hold_expires_at->isPast()) {
            $booking->update(['status' => BookingStatus::Kedaluwarsa]);

            return redirect()->route('public.booking', $kode)
                ->withErrors(['bukti' => 'Waktu tahan sudah habis, unit dilepas kembali.']);
        }

        if ($booking->bukti_path !== null) {
            Storage::disk('local')->delete($booking->bukti_path);
        }

        $extension = $request->file('bukti')->getClientOriginalExtension();
        $path = $request->file('bukti')->storeAs('bukti', $booking->kode.'-'.now()->format('YmdHis').'.'.$extension, 'local');

        $booking->update([
            'bukti_path' => $path,
            'status' => BookingStatus::MenungguVerifikasi,
            'catatan_tolak' => null,
        ]);

        return redirect()->route('public.booking', $kode)
            ->with('status', 'Bukti DP terkirim. Pemilik akan memverifikasi secepatnya.');
    }

    public function bukti(Request $request, Booking $booking): Response|RedirectResponse
    {
        $this->authorize('viewBukti', $booking);

        if ($booking->bukti_path === null || ! Storage::disk('local')->exists($booking->bukti_path)) {
            abort(404);
        }

        $inline = $request->boolean('inline');

        return Storage::disk('local')->response($booking->bukti_path, $inline ? null : basename($booking->bukti_path), [
            'Content-Type' => Storage::disk('local')->mimeType($booking->bukti_path) ?: 'application/octet-stream',
        ], $inline ? 'inline' : 'attachment');
    }
}
