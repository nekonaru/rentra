<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index(): View
    {
        return view('admin.pengumuman', ['pengumuman' => Pengumuman::latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tervalidasi = $request->validate([
            'judul' => ['required', 'string', 'max:160'],
            'isi' => ['required', 'string', 'max:2000'],
            'ditampilkan_sampai' => ['nullable', 'date'],
        ]);

        Pengumuman::create($tervalidasi);

        return back()->with('status', 'Pengumuman dibuat.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->delete();

        return back()->with('status', 'Pengumuman dihapus.');
    }
}
