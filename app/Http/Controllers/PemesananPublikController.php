<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Exceptions\SlotTumpangTindihException;
use App\Http\Requests\SimpanPemesananRequest;
use App\Http\Requests\UnggahBuktiRequest;
use App\Mail\BuktiMasuk;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Services\LayananPemesanan;
use App\Services\PembersihGambar;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemesananPublikController extends Controller
{
    public function __construct(private readonly LayananPemesanan $layananPemesanan, private readonly PembersihGambar $pembersihGambar) {}

    public function simpan(SimpanPemesananRequest $request, string $slug, int $unit): RedirectResponse
    {
        $rental = Rental::aktif()->where('slug', $slug)->firstOrFail();
        $unitModel = $rental->unit()->where('aktif', true)->findOrFail($unit);

        try {
            $pemesanan = $this->layananPemesanan->buat(
                $rental,
                $unitModel,
                $request->validated(),
                StatusPemesanan::Ditahan,
                $request->validated('kode_promo'),
            );
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

        // Ambil ekstensi dari tipe MIME sebenarnya, bukan dari nama file kiriman klien.
        $mime = $request->file('bukti')->getMimeType();
        $perpanjangan = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            default => 'bin',
        };

        $bersih = null;
        if (in_array($mime, ['image/jpeg', 'image/png'], true)) {
            try {
                // Strip EXIF/metadata (GPS, lokasi) dan payload tersembunyi.
                $bersih = $this->pembersihGambar->bersihkan($request->file('bukti')->getRealPath(), $mime);
            } catch (\InvalidArgumentException $e) {
                return back()->withInput()->withErrors(['bukti' => $e->getMessage()]);
            }
        }

        $path = 'bukti/'.$pemesanan->kode.'-'.now()->format('YmdHis').'.'.$perpanjangan;
        Storage::disk('local')->put($path, $bersih ?? file_get_contents($request->file('bukti')->getRealPath()));

        $pemesanan->update([
            'jalur_bukti' => $path,
            'status' => StatusPemesanan::MenungguVerifikasi,
            'catatan_tolak' => null,
        ]);

        // Pemberitahuan ke pemilik. Kegagalan mail tidak boleh menggagalkan upload bukti.
        try {
            $pemesanan->loadMissing(['unit.rental.user']);
            Mail::to($pemesanan->rental->user->email)->send(new BuktiMasuk($pemesanan));
        } catch (\Throwable $e) {
            Log::warning('gagal kirim email bukti masuk', ['kode' => $pemesanan->kode, 'error' => $e->getMessage()]);
        }

        return redirect()->route('publik.pemesanan', $kode)
            ->with('status', 'Bukti DP terkirim. Pemilik akan memverifikasi secepatnya.');
    }

    public function kalender(string $kode): Response
    {
        $pemesanan = Pemesanan::where('kode', $kode)->with('unit')->firstOrFail();

        $format = fn (Carbon $c) => $c->utc()->format('Ymd\THis\Z');
        $isi = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Rentra//ID',
            'BEGIN:VEVENT',
            'UID:'.$pemesanan->kode.'@rentra',
            'DTSTAMP:'.$format(now()),
            'DTSTART:'.$format($pemesanan->mulai),
            'DTEND:'.$format($pemesanan->selesai),
            'SUMMARY:Sewa '.$pemesanan->unit->nama,
            'DESCRIPTION:Kode pemesanan '.$pemesanan->kode,
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        return response($isi, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="'.$pemesanan->kode.'.ics"');
    }

    public function bukti(Request $request, Pemesanan $pemesanan): StreamedResponse|RedirectResponse
    {
        $this->authorize('lihatBukti', $pemesanan);

        if ($pemesanan->jalur_bukti === null || ! Storage::disk('local')->exists($pemesanan->jalur_bukti)) {
            abort(404);
        }

        $mime = Storage::disk('local')->mimeType($pemesanan->jalur_bukti) ?: 'application/octet-stream';
        // Pratinjau sebaris hanya untuk gambar; pdf/file lain selalu diunduh.
        $sebaris = $request->boolean('sebaris') && str_starts_with($mime, 'image/');

        return Storage::disk('local')->response($pemesanan->jalur_bukti, $sebaris ? null : basename($pemesanan->jalur_bukti), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
        ], $sebaris ? 'inline' : 'attachment');
    }
}
