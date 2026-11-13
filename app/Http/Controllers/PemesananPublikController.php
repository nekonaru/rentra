<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Exceptions\SlotTumpangTindihException;
use App\Http\Requests\SimpanPemesananRequest;
use App\Http\Requests\UnggahBuktiRequest;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Services\LayananPemesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PemesananPublikController extends Controller
{
    public function __construct(private readonly LayananPemesanan $layananPemesanan) {}

    public function simpan(SimpanPemesananRequest $request, string $slug, int $unit): RedirectResponse
    {
        $rental = Rental::where('slug', $slug)->firstOrFail();
        $unitModel = $rental->unit()->where('aktif', true)->findOrFail($unit);

        try {
            $pemesanan = $this->layananPemesanan->buat($rental, $unitModel, $request->validated());
        } catch (SlotTumpangTindihException $e) {
            return back()->withInput()->withErrors(['mulai' => $e->getMessage()]);
        }

        return redirect()->route('publik.pemesanan', $pemesanan->kode)
            ->with('status', 'Unit berhasil ditahan. Selesaikan DP sebelum waktu habis.');
    }

    public function tampil(string $kode): View
    {
        $pemesanan = Pemesanan::where('kode', $kode)->with(['unit.rental'])->firstOrFail();

        // Pengaman: hold yang sudah lewat dianggap kedaluwarsa walau scheduler belum jalan.
        if ($pemesanan->status === StatusPemesanan::Ditahan
            && $pemesanan->tahan_sampai !== null
            && $pemesanan->tahan_sampai->isPast()) {
            $pemesanan->update(['status' => StatusPemesanan::Kedaluwarsa]);
            $pemesanan->refresh();
        }

        return view('publik.pemesanan', ['pemesanan' => $pemesanan]);
    }

    public function unggahBukti(UnggahBuktiRequest $request, string $kode): RedirectResponse
    {
        $pemesanan = Pemesanan::where('kode', $kode)->firstOrFail();

        $bolehUnggah = in_array($pemesanan->status, [StatusPemesanan::Ditahan, StatusPemesanan::MenungguVerifikasi], true);
        if (! $bolehUnggah) {
            return redirect()->route('publik.pemesanan', $kode)
                ->withErrors(['bukti' => 'Pemesanan ini tidak bisa menerima bukti DP lagi.']);
        }

        if ($pemesanan->status === StatusPemesanan::Ditahan
            && $pemesanan->tahan_sampai !== null
            && $pemesanan->tahan_sampai->isPast()) {
            $pemesanan->update(['status' => StatusPemesanan::Kedaluwarsa]);

            return redirect()->route('publik.pemesanan', $kode)
                ->withErrors(['bukti' => 'Waktu tahan sudah habis, unit dilepas kembali.']);
        }

        if ($pemesanan->jalur_bukti !== null) {
            Storage::disk('local')->delete($pemesanan->jalur_bukti);
        }

        $perpanjangan = $request->file('bukti')->getClientOriginalExtension();
        $path = $request->file('bukti')->storeAs('bukti', $pemesanan->kode.'-'.now()->format('YmdHis').'.'.$perpanjangan, 'local');

        $pemesanan->update([
            'jalur_bukti' => $path,
            'status' => StatusPemesanan::MenungguVerifikasi,
            'catatan_tolak' => null,
        ]);

        return redirect()->route('publik.pemesanan', $kode)
            ->with('status', 'Bukti DP terkirim. Pemilik akan memverifikasi secepatnya.');
    }

    public function bukti(Request $request, Pemesanan $pemesanan): Response|RedirectResponse
    {
        $this->authorize('lihatBukti', $pemesanan);

        if ($pemesanan->jalur_bukti === null || ! Storage::disk('local')->exists($pemesanan->jalur_bukti)) {
            abort(404);
        }

        $sebaris = $request->boolean('sebaris');

        return Storage::disk('local')->response($pemesanan->jalur_bukti, $sebaris ? null : basename($pemesanan->jalur_bukti), [
            'Content-Type' => Storage::disk('local')->mimeType($pemesanan->jalur_bukti) ?: 'application/octet-stream',
        ], $sebaris ? 'inline' : 'attachment');
    }
}
