<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlockRequest;
use App\Models\Booking;
use App\Models\UnitBlock;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitBlockController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        $blocks = $rental->units()
            ->with('blocks')
            ->get()
            ->flatMap(fn ($unit) => $unit->blocks->map(fn ($block) => [$unit, $block]))
            ->sortByDesc(fn ($pair) => $pair[1]->mulai)
            ->values();

        return view('dashboard.blok', [
            'units' => $rental->units()->orderBy('nama')->get(),
            'blocks' => $blocks,
        ]);
    }

    public function store(BlockRequest $request): RedirectResponse
    {
        $rental = $request->user()->rental;
        $unit = $rental->units()->findOrFail($request->validated('unit_id'));

        $mulai = Carbon::parse($request->validated('mulai'));
        $selesai = Carbon::parse($request->validated('selesai'));

        $conflicts = $unit->bookings()
            ->reserving()
            ->overlapping($mulai, $selesai)
            ->get(['kode', 'nama_penyewa', 'mulai', 'selesai']);

        if ($conflicts->isNotEmpty() && ! $request->boolean('paksa')) {
            $daftar = $conflicts->map(fn (Booking $b) => "{$b->kode} ({$b->nama_penyewa}, {$b->mulai->format('d M H:i')} - {$b->selesai->format('d M H:i')})")->join(', ');

            return back()->withInput()
                ->with('konflik', 'Blok ini bentrok dengan booking aktif: '.$daftar.'. Centang "Tetap blokir" kalau yakin.');
        }

        UnitBlock::create([
            'unit_id' => $unit->id,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'alasan' => $request->validated('alasan'),
        ]);

        return redirect()->route('blok.index')->with('status', 'Blok jadwal tersimpan.');
    }

    public function destroy(Request $request, UnitBlock $unitBlock): RedirectResponse
    {
        $rentalId = $request->user()->rental?->id;

        abort_unless($unitBlock->unit->rental_id === $rentalId, 403);

        $unitBlock->delete();

        return back()->with('status', 'Blok jadwal dihapus.');
    }
}
