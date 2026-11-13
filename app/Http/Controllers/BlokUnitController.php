<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlokRequest;
use App\Models\BlokUnit;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlokUnitController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        $blok = $rental->unit()
            ->with('blok')
            ->get()
            ->flatMap(fn ($unit) => $unit->blok->map(fn ($blok) => [$unit, $blok]))
            ->sortByDesc(fn ($pasangan) => $pasangan[1]->mulai)
            ->values();

        return view('dashboard.blok', [
            'unit' => $rental->unit()->orderBy('nama')->get(),
            'blok' => $blok,
        ]);
    }

    public function simpan(BlokRequest $request): RedirectResponse
    {
        $rental = $request->user()->rental;
        $unit = $rental->unit()->findOrFail($request->validated('unit_id'));

        $mulai = Carbon::parse($request->validated('mulai'));
        $selesai = Carbon::parse($request->validated('selesai'));

        $konflik = $unit->pemesanan()
            ->menahanSlot()
            ->tumpangTindih($mulai, $selesai)
            ->get(['kode', 'nama_penyewa', 'mulai', 'selesai']);

        if ($konflik->isNotEmpty() && ! $request->boolean('paksa')) {
            $daftar = $konflik->map(fn (Pemesanan $p) => "{$p->kode} ({$p->nama_penyewa}, {$p->mulai->format('d M H:i')} - {$p->selesai->format('d M H:i')})")->join(', ');

            return back()->withInput()
                ->with('konflik', 'Blok ini bentrok dengan pemesanan aktif: '.$daftar.'. Centang "Tetap blokir" kalau yakin.');
        }

        BlokUnit::create([
            'unit_id' => $unit->id,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'alasan' => $request->validated('alasan'),
        ]);

        return redirect()->route('blok.index')->with('status', 'Blok jadwal tersimpan.');
    }

    public function hapus(Request $request, BlokUnit $blok): RedirectResponse
    {
        abort_unless($blok->unit->rental_id === $request->user()->rental?->id, 403);

        $blok->delete();

        return back()->with('status', 'Blok jadwal dihapus.');
    }
}
