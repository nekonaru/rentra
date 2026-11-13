<?php

namespace App\Services;

use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Bucket covered minutes per day for a month, then classify each day.
     *
     * @return array<string, array{terpakai_menit:int, state:string}>
     */
    public function ringkasanBulan(Unit $unit, Carbon $bulan): array
    {
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth()->endOfDay();

        $intervals = $this->intervals($unit, $awal, $akhir);

        $ringkasan = [];
        $cursor = $awal->copy();
        while ($cursor->lte($akhir)) {
            $key = $cursor->toDateString();
            $hariAwal = $cursor->copy()->startOfDay();
            $hariAkhir = $cursor->copy()->endOfDay();

            $terpakai = 0;
            foreach ($intervals as [$mulai, $selesai]) {
                $dari = $mulai->greaterThan($hariAwal) ? $mulai : $hariAwal;
                $hingga = $selesai->lessThan($hariAkhir) ? $selesai : $hariAkhir;
                if ($dari->lt($hingga)) {
                    $terpakai += $dari->diffInMinutes($hingga);
                }
            }

            $state = 'tersedia';
            if ($terpakai >= 1440) {
                $state = 'penuh';
            } elseif ($terpakai > 0) {
                $state = 'sebagian';
            }

            $ringkasan[$key] = ['terpakai_menit' => $terpakai, 'state' => $state];
            $cursor->addDay();
        }

        return $ringkasan;
    }

    /**
     * True when no reserving booking and no block overlaps the range.
     */
    public function rentangTersedia(Unit $unit, Carbon $mulai, Carbon $selesai): bool
    {
        $bentrokBooking = $unit->bookings()
            ->reserving()
            ->overlapping($mulai, $selesai)
            ->exists();

        if ($bentrokBooking) {
            return false;
        }

        return ! $unit->blocks()
            ->where('mulai', '<', $selesai)
            ->where('selesai', '>', $mulai)
            ->exists();
    }

    /**
     * @return Collection<int, array{0: Carbon, 1: Carbon}>
     */
    private function intervals(Unit $unit, Carbon $awal, Carbon $akhir): Collection
    {
        $bookings = $unit->bookings()
            ->reserving()
            ->where('mulai', '<', $akhir)
            ->where('selesai', '>', $awal)
            ->get(['mulai', 'selesai']);

        $blocks = $unit->blocks()
            ->where('mulai', '<', $akhir)
            ->where('selesai', '>', $awal)
            ->get(['mulai', 'selesai']);

        return $bookings->concat($blocks)
            ->map(fn ($row) => [Carbon::parse($row->mulai), Carbon::parse($row->selesai)])
            ->values();
    }
}
