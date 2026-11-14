<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanRentalRequest;
use App\Http\Requests\UnitRequest;
use App\Services\PengelolaUnit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(private readonly PengelolaUnit $pengelolaUnit) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.index');
        }

        if ($request->user()->rental()->exists()) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.buat');
    }

    public function simpan(SimpanRentalRequest $request): RedirectResponse
    {
        $rental = $request->user()->rental()->create([
            'nama' => $request->validated('nama'),
            'slug' => $request->validated('slug'),
            'no_wa' => $request->validated('no_wa'),
            'info_rekening' => $request->validated('info_rekening'),
            'durasi_tahan_menit' => $request->validated('durasi_tahan_menit'),
            'persen_dp' => $request->validated('persen_dp'),
        ]);

        return redirect()->route('onboarding.unit')
            ->with('status', 'Rental "'.$rental->nama.'" berhasil dibuat. Sekarang tambahkan unit pertama kamu.');
    }

    public function unit(Request $request): View
    {
        return view('onboarding.unit', [
            'rental' => $request->user()->rental()->with(['unit.aturanTarif'])->firstOrFail(),
        ]);
    }

    public function simpanUnit(UnitRequest $request): RedirectResponse
    {
        $rental = $request->user()->rental;

        $data = $request->safe()->only(['nama', 'kategori', 'aktif', 'tarif_per_hari', 'tarif_per_jam', 'minimal_sewa_jam', 'toleransi_menit']);
        $data['aktif'] = $request->boolean('aktif', true);

        $unit = $this->pengelolaUnit->buat($rental->id, $data, $request->file('foto'));

        return redirect()->route('onboarding.unit')
            ->with('status', 'Unit "'.$unit->nama.'" tersimpan. Tambah lagi atau lanjut ke dashboard.');
    }
}
