<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function edit(Request $request): View
    {
        return view('dashboard.pengaturan', [
            'rental' => $request->user()->rental()->firstOrFail(),
        ]);
    }

    public function perbarui(Request $request): RedirectResponse
    {
        $rental = $request->user()->rental()->firstOrFail();

        $tervalidasi = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'no_wa' => ['required', 'regex:/^62[0-9]{8,14}$/'],
            'info_rekening' => ['nullable', 'string', 'max:500'],
            'durasi_tahan_menit' => ['required', 'integer', 'min:5', 'max:1440'],
            'persen_dp' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'no_wa.regex' => 'Nomor WA harus format 62 diikuti angka, tanpa spasi atau tanda +.',
        ]);

        $rental->update($tervalidasi);

        return redirect()->route('pengaturan.edit')
            ->with('status', 'Pengaturan rental tersimpan.');
    }
}
