<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\Services\PengelolaUnit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(private readonly PengelolaUnit $pengelolaUnit) {}

    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();
        $q = trim((string) $request->query('q', ''));

        $unit = $rental->unit()
            ->with('aturanTarif')
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%$q%")
                    ->orWhere('kategori', 'like', "%$q%")
                    ->orWhere('merek', 'like', "%$q%");
            }))
            ->orderBy('nama')
            ->get();

        return view('unit.index', ['unit' => $unit, 'q' => $q]);
    }

    public function create(): View
    {
        return view('unit.form', ['unit' => null, 'aturanTarif' => null]);
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        $rental = $request->user()->rental;

        $data = $request->safe()->only(['nama', 'kategori', 'spesifikasi', 'merek', 'tahun', 'aktif', 'tarif_per_hari', 'tarif_per_jam', 'tarif_weekend', 'minimal_sewa_jam', 'toleransi_menit']);
        $data['aktif'] = $request->boolean('aktif', true);

        $unit = $this->pengelolaUnit->buat($rental->id, $data, $request->file('foto'), $request->file('foto_tambahan') ?? []);

        return redirect()->route('unit.index')
            ->with('status', 'Unit "'.$unit->nama.'" berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        $this->authorize('update', $unit);

        return view('unit.form', [
            'unit' => $unit->load(['aturanTarif', 'foto']),
            'aturanTarif' => $unit->aturanTarif,
        ]);
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $unit);

        $data = $request->safe()->only(['nama', 'kategori', 'spesifikasi', 'merek', 'tahun', 'aktif', 'hapus_foto', 'tarif_per_hari', 'tarif_per_jam', 'tarif_weekend', 'minimal_sewa_jam', 'toleransi_menit']);
        $data['aktif'] = $request->boolean('aktif', false);

        $this->pengelolaUnit->perbarui($unit, $data, $request->file('foto'), $request->file('foto_tambahan') ?? []);

        return redirect()->route('unit.index')
            ->with('status', 'Unit "'.$unit->nama.'" berhasil diperbarui.');
    }

    public function destroy(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorize('delete', $unit);

        if ($unit->pemesanan()->exists()) {
            return redirect()->route('unit.index')
                ->with('error', 'Unit ini punya riwayat pemesanan. Nonaktifkan saja, jangan hapus.');
        }

        $unit->delete();

        return redirect()->route('unit.index')
            ->with('status', 'Unit berhasil dihapus.');
    }

    public function gantiAktif(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $unit);

        $unit->update(['aktif' => ! $unit->aktif]);

        return back()->with('status', $unit->aktif ? 'Unit diaktifkan.' : 'Unit dinonaktifkan.');
    }
}
