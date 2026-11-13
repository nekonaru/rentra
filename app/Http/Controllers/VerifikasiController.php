<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Http\Requests\TolakBuktiRequest;
use App\Models\Pemesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        $menunggu = $rental->pemesanan()
            ->with('unit')
            ->where('status', StatusPemesanan::MenungguVerifikasi)
            ->orderBy('created_at')
            ->get();

        $ditahan = $rental->pemesanan()
            ->with('unit')
            ->where('status', StatusPemesanan::Ditahan)
            ->where('tahan_sampai', '>', now())
            ->orderBy('tahan_sampai')
            ->get();

        return view('dashboard.verifikasi', [
            'menunggu' => $menunggu,
            'ditahan' => $ditahan,
        ]);
    }

    public function terima(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $this->authorize('terima', $pemesanan);

        $pemesanan->update([
            'status' => StatusPemesanan::Terkonfirmasi,
            'tahan_sampai' => null,
            'catatan_tolak' => null,
        ]);

        return back()->with('status', 'Pemesanan '.$pemesanan->kode.' dikonfirmasi.');
    }

    public function tolak(TolakBuktiRequest $request, Pemesanan $pemesanan): RedirectResponse
    {
        $this->authorize('tolak', $pemesanan);

        $rental = $request->user()->rental;

        $pemesanan->update([
            'status' => StatusPemesanan::Ditahan,
            'tahan_sampai' => now()->addMinutes($rental->durasi_tahan_menit),
            'catatan_tolak' => $request->validated('catatan_tolak'),
        ]);

        return back()->with('status', 'Bukti ditolak. Penyewa bisa upload ulang dalam '.$rental->durasi_tahan_menit.' menit.');
    }
}
