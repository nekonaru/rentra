<?php

namespace App\Services;

use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LayananKetersediaan
{
    /**
     * Akumulasikan menit terpakai per hari selama sebulan, lalu klasifikasikan
     * setiap hari menjadi tersedia, sebagian, atau penuh.
     *
     * @return array<string, array{terpakai_menit:int, state:string}>
     */
    public function ringkasanBulan(Unit $unit, Carbon $bulan): array
    {
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth()->endOfDay();

        $interval = $this->interval($unit, $awal, $akhir);

        $ringkasan = [];
        $cursor = $awal->copy();
        while ($cursor->lte($akhir)) {
            $key = $cursor->toDateString();
            $hariAwal = $cursor->copy()->startOfDay();
            $hariAkhir = $cursor->copy()->endOfDay();

            $terpakai = 0;
            foreach ($interval as [$mulai, $selesai]) {
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
     * True kalau tidak ada pemesanan yang menahan slot dan tidak ada blok
     * yang tumpang tindih dengan rentang.
     */
    public function rentangTersedia(Unit $unit, Carbon $mulai, Carbon $selesai): bool
    {
        $bentrokPemesanan = $unit->pemesanan()
            ->menahanSlot()
            ->tumpangTindih($mulai, $selesai)
            ->exists();

        if ($bentrokPemesanan) {
            return false;
        }

        return ! $unit->blok()
            ->where('mulai', '<', $selesai)
            ->where('selesai', '>', $mulai)
            ->exists();
    }

    /**
     * @return Collection<int, array{0: Carbon, 1: Carbon}>
     */
    private function interval(Unit $unit, Carbon $awal, Carbon $akhir): Collection
    {
        $pemesanan = $unit->pemesanan()
            ->menahanSlot()
            ->where('mulai', '<', $akhir)
            ->where('selesai', '>', $awal)
            ->get(['mulai', 'selesai']);

        $blok = $unit->blok()
            ->where('mulai', '<', $akhir)
            ->where('selesai', '>', $awal)
            ->get(['mulai', 'selesai']);

        return $pemesanan->concat($blok)
            ->map(fn ($row) => [Carbon::parse($row->mulai), Carbon::parse($row->selesai)])
            ->values();
    }
}
