<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Services\AvailabilityService;
use App\Services\PriceCalculator;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PriceCalculator $priceCalculator,
    ) {}

    public function show(string $slug): View
    {
        $rental = Rental::where('slug', $slug)
            ->with(['units' => fn ($q) => $q->where('aktif', true)->with('priceRule')->orderBy('nama')])
            ->firstOrFail();

        return view('public.show', ['rental' => $rental]);
    }

    public function unit(Request $request, string $slug, int $unit): View
    {
        $rental = Rental::where('slug', $slug)->firstOrFail();
        $unitModel = $rental->units()->where('aktif', true)->with('priceRule')->findOrFail($unit);

        $bulan = $this->parseBulan($request->query('bulan'));

        return view('public.unit', [
            'rental' => $rental,
            'unit' => $unitModel,
            'priceRule' => $unitModel->priceRule,
            'bulan' => $bulan,
            'ringkasan' => $this->availability->ringkasanBulan($unitModel, $bulan),
        ]);
    }

    public function preview(Request $request, string $slug, int $unit): JsonResponse
    {
        $rental = Rental::where('slug', $slug)->firstOrFail();
        $unitModel = $rental->units()->where('aktif', true)->with('priceRule')->findOrFail($unit);

        $validated = $request->validate([
            'mulai' => ['required', 'date', 'after:now'],
            'selesai' => ['required', 'date', 'after:mulai'],
        ]);

        $priceRule = $unitModel->priceRule;
        if ($priceRule === null) {
            throw ValidationException::withMessages(['mulai' => 'Tarif unit belum diatur.']);
        }

        $mulai = Carbon::parse($validated['mulai']);
        $selesai = Carbon::parse($validated['selesai']);

        $breakdown = $this->priceCalculator->hitung(
            (float) $priceRule->tarif_per_hari,
            (float) $priceRule->tarif_per_jam,
            $priceRule->minimal_sewa_jam,
            $priceRule->toleransi_menit,
            $mulai,
            $selesai,
            $rental->persen_dp,
        );

        return response()->json([
            ...$breakdown,
            'tersedia' => $this->availability->rentangTersedia($unitModel, $mulai, $selesai),
        ]);
    }

    private function parseBulan(?string $bulan): Carbon
    {
        try {
            $parsed = $bulan !== null ? Carbon::createFromFormat('Y-m', $bulan) : Carbon::now();
        } catch (\InvalidArgumentException) {
            $parsed = false;
        }

        if (! $parsed instanceof Carbon) {
            $parsed = Carbon::now();
        }

        return $parsed->startOfMonth();
    }
}
