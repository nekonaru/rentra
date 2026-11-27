<?php

namespace App\Services;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Kalkulasi harga murni untuk pemesanan. Tanpa framework, tanpa model.
 * Server adalah sumber kebenaran; pratinjau Alpine di client memanggil
 * endpoint yang memakai class yang sama.
 */
class KalkulatorHarga
{
    /**
     * @return array{hari:int, hari_weekend:int, sisa_jam:float, jam_part:float, total:float, dp_nominal:float, durasi_menit:int, dibebankan_menit:int}
     */
    public function hitung(
        float $tarifPerHari,
        float $tarifPerJam,
        int $minimalSewaJam,
        int $toleransiMenit,
        DateTimeInterface $mulai,
        DateTimeInterface $selesai,
        int $persenDp = 0,
        ?float $tarifWeekend = null,
    ): array {
        $durasiMenit = intdiv($selesai->getTimestamp() - $mulai->getTimestamp(), 60);

        if ($durasiMenit <= 0) {
            throw new InvalidArgumentException('Waktu selesai harus setelah waktu mulai.');
        }

        $dibebankan = max(0, $durasiMenit - $toleransiMenit);
        $minimalMenit = $minimalSewaJam * 60;
        if ($dibebankan < $minimalMenit) {
            $dibebankan = $minimalMenit;
        }

        $hari = intdiv($dibebankan, 1440);
        $sisaMenit = $dibebankan % 1440;
        $sisaJam = $sisaMenit / 60.0;

        $tarifWeekend ??= $tarifPerHari;
        $hariWeekend = 0;
        if ($hari > 0) {
            $cursor = Carbon::parse($mulai)->startOfDay();
            for ($i = 0; $i < $hari; $i++) {
                if (in_array((int) $cursor->format('N'), [6, 7], true)) {
                    $hariWeekend++;
                }
                $cursor->addDay();
            }
        }
        $hariBiasa = $hari - $hariWeekend;

        $jamPart = min($sisaJam * $tarifPerJam, $tarifPerHari);
        $total = ($hariBiasa * $tarifPerHari) + ($hariWeekend * $tarifWeekend) + $jamPart;

        return [
            'hari' => $hari,
            'hari_weekend' => $hariWeekend,
            'sisa_jam' => $sisaJam,
            'jam_part' => round($jamPart, 2),
            'total' => round($total, 2),
            'dp_nominal' => round($total * $persenDp / 100, 2),
            'durasi_menit' => $durasiMenit,
            'dibebankan_menit' => $dibebankan,
        ];
    }

    /**
     * Hitung denda keterlambatan berdasarkan jam terlambat, dibatasi maksimum.
     */
    public function denda(
        float $tarifPerJam,
        float $dendaMaks,
        DateTimeInterface $selesai,
        DateTimeInterface $kembali,
    ): float {
        if ($kembali <= $selesai) {
            return 0.0;
        }

        $menit = intdiv($kembali->getTimestamp() - $selesai->getTimestamp(), 60);
        $jam = max(1, (int) ceil($menit / 60));

        return round(min($jam * $tarifPerJam, $dendaMaks > 0 ? $dendaMaks : PHP_FLOAT_MAX), 2);
    }
}
