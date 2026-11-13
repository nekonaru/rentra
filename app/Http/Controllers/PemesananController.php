<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Models\Pemesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemesananController extends Controller
{
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
                    $p->kode,
                    $p->unit->nama,
                    $p->nama_penyewa,
                    $p->wa,
                    $p->mulai->format('Y-m-d H:i'),
                    $p->selesai->format('Y-m-d H:i'),
                    number_format((float) $p->total, 2, ',', ''),
                    number_format((float) $p->dp_nominal, 2, ',', ''),
                    $p->status->label(),
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
