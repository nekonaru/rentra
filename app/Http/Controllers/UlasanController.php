<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Models\Pemesanan;
use App\Models\Ulasan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    public function simpan(Request $request, string $kode): RedirectResponse
    {
        $pemesanan = Pemesanan::where('kode', $kode)->firstOrFail();

        // Hanya pemesanan selesai yang bisa diulas, dan belum pernah diulas.
        abort_unless($pemesanan->status === StatusPemesanan::Selesai, 422);
        abort_if(Ulasan::where('pemesanan_id', $pemesanan->id)->exists(), 422);

        $tervalidasi = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        Ulasan::create([
            'pemesanan_id' => $pemesanan->id,
            'unit_id' => $pemesanan->unit_id,
            'rental_id' => $pemesanan->rental_id,
            'rating' => $tervalidasi['rating'],
            'komentar' => $tervalidasi['komentar'],
            'status' => 'menunggu',
        ]);

        return back()->with('status', 'Terima kasih. Ulasan kamu menunggu persetujuan pemilik.');
    }

    public function moderasiIndex(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();
        $ulasan = $rental->ulasan()->with('unit')->latest()->get();

        return view('ulasan.index', ['ulasan' => $ulasan]);
    }

    public function moderasi(Request $request, Ulasan $ulasan): RedirectResponse
    {
        $rental = $request->user()->rental()->firstOrFail();
        abort_unless($ulasan->rental_id === $rental->id, 403);

        $tervalidasi = $request->validate([
            'status' => ['required', 'in:tampil,tolak'],
        ]);

        $ulasan->update(['status' => $tervalidasi['status']]);

        return back()->with('status', $tervalidasi['status'] === 'tampil' ? 'Ulasan ditampilkan.' : 'Ulasan ditolak.');
    }
}
