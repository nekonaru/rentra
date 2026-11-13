<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Exceptions\SlotTumpangTindihException;
use App\Models\Pemesanan;
use App\Services\LayananPemesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemesananController extends Controller
{
    public function __construct(private readonly LayananPemesanan $layananPemesanan) {}

    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        $berjalan = $rental->pemesanan()
            ->with('unit')
            ->whereIn('status', [
                StatusPemesanan::Terkonfirmasi->value,
                StatusPemesanan::SedangDisewa->value,
                StatusPemesanan::Terlambat->value,
            ])
            ->orderBy('mulai')
            ->get();

        $riwayat = $rental->pemesanan()
            ->with('unit')
            ->whereIn('status', [
                StatusPemesanan::Selesai->value,
                StatusPemesanan::Kedaluwarsa->value,
                StatusPemesanan::Dibatalkan->value,
            ])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('dashboard.pemesanan', [
            'berjalan' => $berjalan,
            'riwayat' => $riwayat,
        ]);
    }

    public function tampilTambah(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        return view('dashboard.pemesanan-tambah', [
            'unit' => $rental->unit()->where('aktif', true)->with('aturanTarif')->orderBy('nama')->get(),
        ]);
    }

    public function tambah(Request $request): RedirectResponse
    {
        $rental = $request->user()->rental()->firstOrFail();

        $tervalidasi = $request->validate([
            'unit_id' => ['required', 'integer'],
            'nama_penyewa' => ['required', 'string', 'max:120'],
            'wa' => ['required', 'regex:/^62[0-9]{8,14}$/'],
            'mulai' => ['required', 'date'],
            'selesai' => ['required', 'date', 'after:mulai'],
        ], [
            'wa.regex' => 'Nomor WA harus format 62 diikuti angka, tanpa spasi atau tanda +.',
        ]);

        $unit = $rental->unit()->where('aktif', true)->findOrFail($tervalidasi['unit_id']);

        try {
            $pemesanan = $this->layananPemesanan->buat($rental, $unit, $tervalidasi, StatusPemesanan::Terkonfirmasi);
        } catch (SlotTumpangTindihException $e) {
            return back()->withInput()->withErrors(['mulai' => $e->getMessage()]);
        }

        return redirect()->route('pemesanan.index')
            ->with('status', 'Pemesanan '.$pemesanan->kode.' dibuat dan langsung dikonfirmasi.');
    }

    public function cetak(Request $request, Pemesanan $pemesanan): View
    {
        $this->authorize('kelola', $pemesanan);

        return view('dashboard.cetak', [
            'pemesanan' => $pemesanan->load(['unit', 'rental']),
        ]);
    }

    private function csvAman(string $value): string
    {
        // Cegah formula injection saat dibuka di Excel/LibreOffice.
        if (preg_match('/^[=+\-@]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }

    public function ekspor(Request $request): StreamedResponse
    {
        $rental = $request->user()->rental()->firstOrFail();

        $pemesanan = $rental->pemesanan()->with('unit')->orderBy('mulai')->get();

        $filename = 'pemesanan-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($pemesanan) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, ['Kode', 'Unit', 'Penyewa', 'WA', 'Mulai', 'Selesai', 'Total', 'DP', 'Status'], ';');

            foreach ($pemesanan as $p) {
                fputcsv($out, [
                    $this->csvAman($p->kode),
                    $this->csvAman($p->unit->nama),
                    $this->csvAman($p->nama_penyewa),
                    $this->csvAman($p->wa),
                    $this->csvAman($p->mulai->format('Y-m-d H:i')),
                    $this->csvAman($p->selesai->format('Y-m-d H:i')),
                    $this->csvAman(number_format((float) $p->total, 2, ',', '')),
                    $this->csvAman(number_format((float) $p->dp_nominal, 2, ',', '')),
                    $this->csvAman($p->status->label()),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public function serahTerima(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $this->authorize('kelola', $pemesanan);

        abort_unless($pemesanan->status === StatusPemesanan::Terkonfirmasi, 422);

        $pemesanan->update(['status' => StatusPemesanan::SedangDisewa]);

        return back()->with('status', 'Pemesanan '.$pemesanan->kode.' ditandai sedang disewa.');
    }

    public function selesai(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $this->authorize('kelola', $pemesanan);

        abort_unless(in_array($pemesanan->status, [StatusPemesanan::SedangDisewa, StatusPemesanan::Terlambat], true), 422);

        $pemesanan->update(['status' => StatusPemesanan::Selesai]);

        return back()->with('status', 'Pemesanan '.$pemesanan->kode.' ditandai selesai.');
    }

    public function batal(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $this->authorize('kelola', $pemesanan);

        abort_unless(in_array($pemesanan->status, [StatusPemesanan::Ditahan, StatusPemesanan::MenungguVerifikasi, StatusPemesanan::Terkonfirmasi], true), 422);

        $pemesanan->update(['status' => StatusPemesanan::Dibatalkan, 'tahan_sampai' => null]);

        return back()->with('status', 'Pemesanan '.$pemesanan->kode.' dibatalkan.');
    }
}
