<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Services\KalkulatorHarga;
use App\Services\LayananKetersediaan;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublikController extends Controller
{
    public function __construct(
        private readonly LayananKetersediaan $ketersediaan,
        private readonly KalkulatorHarga $kalkulatorHarga,
    ) {}

    public function tampil(string $slug): View
    {
        $rental = Rental::aktif()->where('slug', $slug)
            ->with(['unit' => fn ($q) => $q->where('aktif', true)->with('aturanTarif')->orderBy('nama')])
            ->firstOrFail();

        return view('publik.tampil', ['rental' => $rental]);
    }

    public function unit(Request $request, string $slug, int $unit): View
    {
        $rental = Rental::aktif()->where('slug', $slug)->firstOrFail();
        $unitModel = $rental->unit()->where('aktif', true)->with('aturanTarif')->findOrFail($unit);

        $bulan = $this->uraiBulan($request->query('bulan'));

        $ulasan = $unitModel->ulasan()->latest()->take(5)->get();
        $ratingRata = (float) ($unitModel->ulasan()->avg('rating') ?? 0);

        return view('publik.unit', [
            'rental' => $rental,
            'unit' => $unitModel,
            'aturanTarif' => $unitModel->aturanTarif,
            'bulan' => $bulan,
            'ringkasan' => $this->ketersediaan->ringkasanBulan($unitModel, $bulan),
            'ulasan' => $ulasan,
            'ratingRata' => $ratingRata,
        ]);
    }

    public function pratinjauHarga(Request $request, string $slug, int $unit): JsonResponse
    {
        $rental = Rental::aktif()->where('slug', $slug)->firstOrFail();
        $unitModel = $rental->unit()->where('aktif', true)->with('aturanTarif')->findOrFail($unit);

        $tervalidasi = $request->validate([
            'mulai' => ['required', 'date', 'after:now'],
            'selesai' => ['required', 'date', 'after:mulai'],
        ]);

        $aturanTarif = $unitModel->aturanTarif;
        if ($aturanTarif === null) {
            throw ValidationException::withMessages(['mulai' => 'Tarif unit belum diatur.']);
        }

        $mulai = Carbon::parse($tervalidasi['mulai']);
        $selesai = Carbon::parse($tervalidasi['selesai']);

        $rincian = $this->kalkulatorHarga->hitung(
            (float) $aturanTarif->tarif_per_hari,
            (float) $aturanTarif->tarif_per_jam,
            $aturanTarif->minimal_sewa_jam,
            $aturanTarif->toleransi_menit,
            $mulai,
            $selesai,
            $rental->persen_dp,
        );

        return response()->json([
            ...$rincian,
            'tersedia' => $this->ketersediaan->rentangTersedia($unitModel, $mulai, $selesai),
        ]);
    }

    private function uraiBulan(?string $bulan): Carbon
    {
        try {
            $parsed = $bulan !== null ? Carbon::createFromFormat('Y-m', $bulan) : Carbon::now();
        } catch (\Throwable) {
            $parsed = false;
        }

        if (! $parsed instanceof Carbon) {
            $parsed = Carbon::now();
        }

        return $parsed->startOfMonth();
    }
}
