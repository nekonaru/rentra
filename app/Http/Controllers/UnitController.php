<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\Services\UnitManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(private readonly UnitManager $unitManager) {}

    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->with(['units.priceRule'])->firstOrFail();

        return view('units.index', ['units' => $rental->units()->with('priceRule')->orderBy('nama')->get()]);
    }

    public function create(): View
    {
        return view('units.form', ['unit' => null, 'priceRule' => null]);
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        $rental = $request->user()->rental;

        $data = $request->safe()->only(['nama', 'kategori', 'aktif', 'tarif_per_hari', 'tarif_per_jam', 'minimal_sewa_jam', 'toleransi_menit']);
        $data['aktif'] = $request->boolean('aktif', true);

        $unit = $this->unitManager->create($rental->id, $data, $request->file('foto'));

        return redirect()->route('units.index')
            ->with('status', 'Unit "'.$unit->nama.'" berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        $this->authorize('update', $unit);

        return view('units.form', [
            'unit' => $unit->load('priceRule'),
            'priceRule' => $unit->priceRule,
        ]);
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $unit);

        $data = $request->safe()->only(['nama', 'kategori', 'aktif', 'hapus_foto', 'tarif_per_hari', 'tarif_per_jam', 'minimal_sewa_jam', 'toleransi_menit']);
        $data['aktif'] = $request->boolean('aktif', false);

        $this->unitManager->update($unit, $data, $request->file('foto'));

        return redirect()->route('units.index')
            ->with('status', 'Unit "'.$unit->nama.'" berhasil diperbarui.');
    }

    public function destroy(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorize('delete', $unit);

        if ($unit->bookings()->exists()) {
            return redirect()->route('units.index')
                ->with('error', 'Unit ini punya riwayat booking. Nonaktifkan saja, jangan hapus.');
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('status', 'Unit berhasil dihapus.');
    }

    public function toggleAktif(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $unit);

        $unit->update(['aktif' => ! $unit->aktif]);

        return back()->with('status', $unit->aktif ? 'Unit diaktifkan.' : 'Unit dinonaktifkan.');
    }
}
