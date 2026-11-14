<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $statusDiakui = [
            StatusPemesanan::Selesai->value,
            StatusPemesanan::SedangDisewa->value,
            StatusPemesanan::Terlambat->value,
        ];

        $statistik = [
            'pemilik' => User::where('role', 'pemilik')->count(),
            'rentalAktif' => Rental::where('aktif', true)->count(),
            'rentalDitangguhkan' => Rental::where('aktif', false)->count(),
            'unit' => Unit::count(),
            'pemesanan' => Pemesanan::count(),
            'pemesananHariIni' => Pemesanan::whereDate('created_at', today())->count(),
            'omzetTotal' => (float) Pemesanan::whereIn('status', $statusDiakui)->sum('total'),
            'menungguVerifikasi' => Pemesanan::where('status', StatusPemesanan::MenungguVerifikasi)->count(),
        ];

        $terbaru = Rental::with('user')
            ->withCount(['unit', 'pemesanan'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.index', [
            'statistik' => $statistik,
            'terbaru' => $terbaru,
        ]);
    }

    public function tenant(Request $request): View
    {
        $statusDiakui = [
            StatusPemesanan::Selesai->value,
            StatusPemesanan::SedangDisewa->value,
            StatusPemesanan::Terlambat->value,
        ];

        $tenant = Rental::with('user')
            ->withCount(['unit', 'pemesanan'])
            ->withSum(['pemesanan' => fn ($q) => $q->whereIn('status', $statusDiakui)], 'total')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.tenant', ['tenant' => $tenant]);
    }

    public function tenantShow(Request $request, Rental $rental): View
    {
        $statusDiakui = [
            StatusPemesanan::Selesai->value,
            StatusPemesanan::SedangDisewa->value,
            StatusPemesanan::Terlambat->value,
        ];

        $rental->load('user')->loadCount(['unit', 'pemesanan']);

        $perStatus = $rental->pemesanan()
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $ringkasan = [
            'omzet' => (float) $rental->pemesanan()->whereIn('status', $statusDiakui)->sum('total'),
            'dpTerkumpul' => (float) $rental->pemesanan()->whereIn('status', [
                StatusPemesanan::Terkonfirmasi->value,
                StatusPemesanan::SedangDisewa->value,
                StatusPemesanan::Selesai->value,
                StatusPemesanan::Terlambat->value,
            ])->sum('dp_nominal'),
            'unitAktif' => $rental->unit()->where('aktif', true)->count(),
            'perStatus' => $perStatus,
        ];

        return view('admin.tenant-detail', [
            'rental' => $rental,
            'ringkasan' => $ringkasan,
        ]);
    }

    public function tangguhkan(Request $request, Rental $rental): RedirectResponse
    {
        $rental->update(['aktif' => false]);

        return back()->with('status', 'Rental "'.$rental->nama.'" ditangguhkan. Halaman publiknya tidak tampil.');
    }

    public function aktifkan(Request $request, Rental $rental): RedirectResponse
    {
        $rental->update(['aktif' => true]);

        return back()->with('status', 'Rental "'.$rental->nama.'" diaktifkan kembali.');
    }
}
