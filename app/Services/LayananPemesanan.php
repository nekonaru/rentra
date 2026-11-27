<?php

namespace App\Services;

use App\Enums\StatusPemesanan;
use App\Exceptions\SlotTumpangTindihException;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LayananPemesanan
{
    public function __construct(
        private readonly KalkulatorHarga $kalkulatorHarga,
        private readonly LayananKetersediaan $ketersediaan,
    ) {}

    /**
     * Buat pemesanan di dalam transaksi dengan row lock di tabel unit,
     * supaya pemesanan bersamaan untuk unit yang sama berjalan berurutan.
     *
     * @param  array{mulai: string, selesai: string, nama_penyewa: string, wa: string}  $data
     */
    public function buat(Rental $rental, Unit $unit, array $data, StatusPemesanan $statusAwal = StatusPemesanan::Ditahan): Pemesanan
    {
        return DB::transaction(function () use ($rental, $unit, $data, $statusAwal) {
            // Kunci baris unit. Pemesanan lain untuk unit ini menunggu di sini.
            $terkunci = Unit::whereKey($unit->id)->lockForUpdate()->firstOrFail();

            $mulai = Carbon::parse($data['mulai']);
            $selesai = Carbon::parse($data['selesai']);

            if (! $this->ketersediaan->rentangTersedia($terkunci, $mulai, $selesai)) {
                throw new SlotTumpangTindihException('Jadwal ini baru saja diambil, pilih waktu lain.');
            }

            $aturanTarif = $terkunci->aturanTarif;
            if ($aturanTarif === null) {
                throw new SlotTumpangTindihException('Tarif unit belum diatur.');
            }

            $rincian = $this->kalkulatorHarga->hitung(
                (float) $aturanTarif->tarif_per_hari,
                (float) $aturanTarif->tarif_per_jam,
                $aturanTarif->minimal_sewa_jam,
                $aturanTarif->toleransi_menit,
                $mulai,
                $selesai,
                $rental->persen_dp,
                $aturanTarif->tarif_weekend !== null ? (float) $aturanTarif->tarif_weekend : null,
            );

            return Pemesanan::create([
                'kode' => $this->kodeUnik(),
                'unit_id' => $terkunci->id,
                'rental_id' => $rental->id,
                'nama_penyewa' => $data['nama_penyewa'],
                'wa' => $data['wa'],
                'mulai' => $mulai,
                'selesai' => $selesai,
                'total' => $rincian['total'],
                'dp_nominal' => $rincian['dp_nominal'],
                'status' => $statusAwal,
                'tahan_sampai' => $statusAwal === StatusPemesanan::Ditahan
                    ? now()->addMinutes($rental->durasi_tahan_menit)
                    : null,
            ]);
        });
    }

    /**
     * Tandai pemesanan yang hold-nya habis jadi kedaluwarsa, dan yang lewat
     * jam selesai jadi terlambat. Idempotent.
     */
    public function sapuKedaluwarsa(): int
    {
        $kedaluwarsa = Pemesanan::query()
            ->where('status', StatusPemesanan::Ditahan->value)
            ->whereNotNull('tahan_sampai')
            ->where('tahan_sampai', '<=', now())
            ->update(['status' => StatusPemesanan::Kedaluwarsa->value]);

        $terlambat = Pemesanan::query()
            ->where('status', StatusPemesanan::SedangDisewa->value)
            ->where('selesai', '<=', now())
            ->update(['status' => StatusPemesanan::Terlambat->value]);

        return $kedaluwarsa + $terlambat;
    }

    private function kodeUnik(): string
    {
        do {
            $kode = strtoupper(Str::random(10));
        } while (Pemesanan::where('kode', $kode)->exists());

        return $kode;
    }
}
