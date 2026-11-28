<?php

namespace App\Http\Controllers;

use App\Enums\StatusPemesanan;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use App\Support\SlugTerlarang;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $statusDiakui = [
            StatusPemesanan::Selesai->value,
            StatusPemesanan::SedangDisewa->value,
            StatusPemesanan::Terlambat->value,
        ];

        // Pertumbuhan tenant 6 bulan terakhir.
        $pertumbuhan = Rental::query()
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at'])
            ->groupBy(fn ($r) => $r->created_at->format('Y-m'))
            ->map(fn ($rows) => $rows->count())
            ->all();

        $bulanData = collect(range(5, 0))->map(function (int $mundur) use ($pertumbuhan) {
            $bulan = now()->subMonths($mundur);

            return [
                'label' => $bulan->translatedFormat('M Y'),
                'jumlah' => $pertumbuhan[$bulan->format('Y-m')] ?? 0,
            ];
        });
        $maxBulan = max(1, (int) $bulanData->max('jumlah'));

        // Tenang pasif: tidak ada pemesanan 30 hari terakhir.
        $tenantPasif = Rental::where('aktif', true)
            ->whereDoesntHave('pemesanan', fn ($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->count();

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
            'bulanData' => $bulanData,
            'maxBulan' => $maxBulan,
            'tenantPasif' => $tenantPasif,
        ]);
    }

    public function tenant(Request $request): View
    {
        $statusDiakui = [
            StatusPemesanan::Selesai->value,
            StatusPemesanan::SedangDisewa->value,
            StatusPemesanan::Terlambat->value,
        ];

        $q = trim((string) $request->query('q', ''));
        $perPage = 15;

        $tenant = Rental::with('user')
            ->withCount(['unit', 'pemesanan'])
            ->withSum(['pemesanan' => fn ($q) => $q->whereIn('status', $statusDiakui)], 'total')
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%$q%")
                    ->orWhere('slug', 'like', "%$q%")
                    ->orWhere('no_wa', 'like', "%$q%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%"));
            }))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.tenant', ['tenant' => $tenant, 'q' => $q]);
    }

    public function tenantCreate(Request $request): View
    {
        return view('admin.tenant-form');
    }

    public function tenantSimpan(Request $request): RedirectResponse
    {
        $tervalidasi = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'not_in:'.implode(',', SlugTerlarang::daftar()), 'unique:rental,slug'],
            'no_wa' => ['required', 'regex:/^62[0-9]{8,14}$/'],
            'nama_pemilik' => ['required', 'string', 'max:120'],
            'email_pemilik' => ['required', 'email', 'max:191', 'unique:users,email'],
        ], [
            'slug.unique' => 'Slug sudah dipakai rental lain.',
            'slug.not_in' => 'Slug ini dipakai sistem.',
            'slug.regex' => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung.',
            'no_wa.regex' => 'Nomor WA harus format 62 diikuti angka, tanpa spasi atau tanda +.',
            'email_pemilik.unique' => 'Email pemilik sudah terdaftar.',
        ]);

        $password = bin2hex(random_bytes(9)); // 18 karakter acak

        $pemilik = User::create([
            'name' => $tervalidasi['nama_pemilik'],
            'email' => strtolower($tervalidasi['email_pemilik']),
            'password' => $password,
            'role' => 'pemilik',
            'email_verified_at' => now(),
        ]);

        Rental::create([
            'user_id' => $pemilik->id,
            'nama' => $tervalidasi['nama'],
            'slug' => $tervalidasi['slug'],
            'no_wa' => $tervalidasi['no_wa'],
            'durasi_tahan_menit' => 60,
            'persen_dp' => 50,
            'aktif' => true,
        ]);

        Log::channel('keamanan')->info('admin_membuat_tenant', [
            'admin_id' => $request->user()->id,
            'slug' => $tervalidasi['slug'],
            'email_pemilik' => $pemilik->email,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.tenant.index')
            ->with('status', 'Tenant "'.$tervalidasi['nama'].'" berhasil dibuat. Bagikan kredensial ini ke pemilik SEKARANG (tidak bisa dilihat lagi):')
            ->with('password_baru', $password)
            ->with('kredensial', [
                'email_pemilik' => $pemilik->email,
                'slug' => $tervalidasi['slug'],
            ]);
    }

    public function ekspor(Request $request): StreamedResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $tenant = Rental::with('user')
            ->withCount(['unit', 'pemesanan'])
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($tenant) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Rental', 'Slug', 'Pemilik', 'Email', 'WA', 'Unit', 'Pemesanan', 'Status'], ';');
            foreach ($tenant as $r) {
                fputcsv($out, [
                    $r->nama, $r->slug, $r->user->name, $r->user->email, $r->no_wa,
                    $r->unit_count, $r->pemesanan_count, $r->aktif ? 'Aktif' : 'Ditangguhkan',
                ], ';');
            }
            fclose($out);
        }, 'daftar-tenant-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
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

        Log::channel('keamanan')->warning('admin_menangguhkan_tenant', [
            'admin_id' => $request->user()->id,
            'rental_id' => $rental->id,
            'slug' => $rental->slug,
            'ip' => $request->ip(),
        ]);

        return back()->with('status', 'Rental "'.$rental->nama.'" ditangguhkan. Halaman publiknya tidak tampil.');
    }

    public function aktifkan(Request $request, Rental $rental): RedirectResponse
    {
        $rental->update(['aktif' => true]);

        Log::channel('keamanan')->info('admin_mengaktifkan_tenant', [
            'admin_id' => $request->user()->id,
            'rental_id' => $rental->id,
            'slug' => $rental->slug,
            'ip' => $request->ip(),
        ]);

        return back()->with('status', 'Rental "'.$rental->nama.'" diaktifkan kembali.');
    }
}
