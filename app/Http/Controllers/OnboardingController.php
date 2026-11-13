<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\UnitRequest;
use App\Services\UnitManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(private readonly UnitManager $unitManager) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->rental()->exists()) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.create');
    }

    public function store(StoreRentalRequest $request): RedirectResponse
    {
        $rental = $request->user()->rental()->create([
            'nama' => $request->validated('nama'),
            'slug' => $request->validated('slug'),
            'no_wa' => $request->validated('no_wa'),
            'info_rekening' => $request->validated('info_rekening'),
            'durasi_hold_menit' => $request->validated('durasi_hold_menit'),
            'persen_dp' => $request->validated('persen_dp'),
        ]);

        return redirect()->route('onboarding.unit')
            ->with('status', 'Rental "'.$rental->nama.'" berhasil dibuat. Sekarang tambahkan unit pertama kamu.');
    }

    public function unit(Request $request): View
    {
        return view('onboarding.unit', [
            'rental' => $request->user()->rental()->with(['units.priceRule'])->firstOrFail(),
        ]);
    }

    public function storeUnit(UnitRequest $request): RedirectResponse
    {
        $rental = $request->user()->rental;

        $data = $request->safe()->only(['nama', 'kategori', 'aktif', 'tarif_per_hari', 'tarif_per_jam', 'minimal_sewa_jam', 'toleransi_menit']);
        $data['aktif'] = $request->boolean('aktif', true);

        $unit = $this->unitManager->create($rental->id, $data, $request->file('foto'));

        return redirect()->route('onboarding.unit')
            ->with('status', 'Unit "'.$unit->nama.'" tersimpan. Tambah lagi atau lanjut ke dashboard.');
    }
}
