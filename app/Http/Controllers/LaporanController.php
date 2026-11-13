<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        $statusDiakui = [
            StatusPemesanan::Selesai->value,
            StatusPemesanan::SedangDisewa->value,
            StatusPemesanan::Terlambat->value,
        ];

        // Omzet 6 bulan terakhir, dihitung di PHP supaya jalan di MySQL maupun SQLite (test).
        $selesaiRows = $rental->pemesanan()
            ->whereIn('status', $statusDiakui)
            ->where('selesai', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['selesai', 'total']);

        $perBulan = $selesaiRows
            ->groupBy(fn ($p) => $p->selesai->format('Y-m'))
            ->map(fn ($rows) => ['omzet' => (float) $rows->sum('total'), 'jumlah' => $rows->count()])
            ->all();

        $bulanData = collect(range(5, 0))->map(function (int $mundur) use ($perBulan) {
            $bulan = now()->subMonths($mundur);
            $key = $bulan->format('Y-m');
            $row = $perBulan[$key] ?? ['omzet' => 0.0, 'jumlah' => 0];

            return [
                'label' => $bulan->translatedFormat('M Y'),
                'omzet' => $row['omzet'],
                'jumlah' => $row['jumlah'],
            ];
        });

        $maxOmzet = max(1.0, (float) $bulanData->max('omzet'));

        // Okupasi 30 hari terakhir: menit terpakai / (unit aktif x 30 hari).
        $unitAktif = $rental->unit()->where('aktif', true)->count();
        $windowAwal = now()->subDays(30);
        $terpakaiMenit = $this->menitTerpakai($rental, $windowAwal, now());
        $okupasi = $unitAktif > 0 ? min(100.0, round($terpakaiMenit / ($unitAktif * 30 * 1440) * 100, 1)) : 0.0;

        $unitTerlaris = $rental->pemesanan()
            ->whereIn('status', $statusDiakui)
            ->selectRaw('unit_id, SUM(total) as omzet, COUNT(*) as jumlah')
            ->groupBy('unit_id')
            ->orderByDesc('omzet')
            ->with('unit')
            ->take(5)
            ->get();

        $statistik = [
            'omzetBulanIni' => (float) $rental->pemesanan()
                ->whereIn('status', $statusDiakui)
                ->whereMonth('selesai', now()->month)
                ->whereYear('selesai', now()->year)
                ->sum('total'),
            'rataRataNilai' => (float) $rental->pemesanan()->whereIn('status', $statusDiakui)->avg('total'),
            'dpTerkumpul' => (float) $rental->pemesanan()
                ->whereIn('status', [
                    StatusPemesanan::Terkonfirmasi->value,
                    StatusPemesanan::SedangDisewa->value,
                    StatusPemesanan::Selesai->value,
                    StatusPemesanan::Terlambat->value,
                ])
                ->sum('dp_nominal'),
            'okupasi' => $okupasi,
        ];

        return view('dashboard.laporan', [
            'rental' => $rental,
            'bulanData' => $bulanData,
            'maxOmzet' => $maxOmzet,
            'unitTerlaris' => $unitTerlaris,
            'statistik' => $statistik,
        ]);
    }

    private function menitTerpakai(Rental $rental, Carbon $awal, Carbon $akhir): int
    {
        $interval = $rental->pemesanan()
            ->menahanSlot()
            ->where('mulai', '<', $akhir)
            ->where('selesai', '>', $awal)
            ->get(['mulai', 'selesai'])
            ->map(fn ($p) => [Carbon::parse($p->mulai), Carbon::parse($p->selesai)]);

        $total = 0;
        foreach ($interval as [$mulai, $selesai]) {
            $dari = $mulai->greaterThan($awal) ? $mulai : $awal;
            $hingga = $selesai->lessThan($akhir) ? $selesai : $akhir;
            if ($dari->lt($hingga)) {
                $total += $dari->diffInMinutes($hingga);
            }
        }

        return $total;
    }
}
