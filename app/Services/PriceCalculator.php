<?php

namespace App\Services;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * Pure price calculation for rentals. No framework dependency, no models.
 * Server is the source of truth; the Alpine preview on the client calls an
 * endpoint that uses this same class.
 */
class PriceCalculator
{
    /**
     * @return array{hari:int, sisa_jam:float, jam_part:float, total:float, dp_nominal:float, durasi_menit:int, dibebankan_menit:int}
     */
    public function hitung(
        float $tarifPerHari,
        float $tarifPerJam,
        int $minimalSewaJam,
        int $toleransiMenit,
        DateTimeInterface $mulai,
        DateTimeInterface $selesai,
        int $persenDp = 0,
    ): array {
        $durasiMenit = intdiv($selesai->getTimestamp() - $mulai->getTimestamp(), 60);

        if ($durasiMenit <= 0) {
            throw new InvalidArgumentException('Waktu selesai harus setelah waktu mulai.');
        }

        // Toleransi: short overruns are not billed.
        $dibebankan = max(0, $durasiMenit - $toleransiMenit);

        // Minimal sewa: at least minimalSewaJam hours are billed.
        $minimalMenit = $minimalSewaJam * 60;
        if ($dibebankan < $minimalMenit) {
            $dibebankan = $minimalMenit;
        }

        $hari = intdiv($dibebankan, 1440);
        $sisaMenit = $dibebankan % 1440;
        $sisaJam = $sisaMenit / 60.0;

        $jamPart = min($sisaJam * $tarifPerJam, $tarifPerHari);
        $total = ($hari * $tarifPerHari) + $jamPart;

        return [
            'hari' => $hari,
            'sisa_jam' => $sisaJam,
            'jam_part' => round($jamPart, 2),
            'total' => round($total, 2),
            'dp_nominal' => round($total * $persenDp / 100, 2),
            'durasi_menit' => $durasiMenit,
            'dibebankan_menit' => $dibebankan,
        ];
    }
}
