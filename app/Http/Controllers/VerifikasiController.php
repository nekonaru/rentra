<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Http\Requests\TolakBuktiRequest;
use App\Models\Pemesanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'jumlahBukti' => $rental->pemesanan()->whereNotNull('jalur_bukti')->count(),
        ]);
    }

    public function eksporBukti(Request $request): BinaryFileResponse
    {
        $rental = $request->user()->rental()->firstOrFail();

        $bukti = $rental->pemesanan()->whereNotNull('jalur_bukti')->get(['kode', 'jalur_bukti']);

        abort_if($bukti->isEmpty(), 404, 'Belum ada bukti DP yang bisa diekspor.');

        Storage::disk('local')->makeDirectory('tmp-ekspor');
        $tmp = Storage::disk('local')->path('tmp-ekspor/bukti-dp-'.now()->format('Ymd-His').'.zip');

        $zip = new \ZipArchive;
        abort_unless($zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true, 500);

        foreach ($bukti as $row) {
            $full = Storage::disk('local')->path($row->jalur_bukti);
            if (is_file($full)) {
                $zip->addFile($full, basename($row->jalur_bukti));
            }
        }

        $zip->close();

        return response()->download($tmp, 'bukti-dp-'.now()->format('Y-m-d').'.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
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
