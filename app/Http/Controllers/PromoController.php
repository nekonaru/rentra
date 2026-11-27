<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(Request $request): View
    {
        $rental = $request->user()->rental()->firstOrFail();

        return view('promo.index', ['promo' => $rental->promo()->latest()->get()]);
    }

    public function buat(Request $request): RedirectResponse
    {
        $rental = $request->user()->rental()->firstOrFail();

        $tervalidasi = $request->validate([
            'kode' => ['required', 'string', 'max:32', 'regex:/^[A-Z0-9]+$/'],
            'jenis' => ['required', 'in:persen,nominal'],
            'nilai' => ['required', 'numeric', 'min:0'],
            'minimal_sewa_jam' => ['required', 'integer', 'min:1'],
            'kuota' => ['required', 'integer', 'min:1'],
        ]);

        if ($tervalidasi['jenis'] === 'persen' && $tervalidasi['nilai'] > 100) {
            return back()->withInput()->withErrors(['nilai' => 'Diskon persen maksimal 100.']);
        }

        $rental->promo()->create($tervalidasi);

        return back()->with('status', 'Promo '.$tervalidasi['kode'].' berhasil dibuat.');
    }

    public function hapus(Request $request, Promo $promo): RedirectResponse
    {
        abort_unless($promo->rental_id === $request->user()->rental?->id, 403);
        $promo->delete();

        return back()->with('status', 'Promo dihapus.');
    }
}
