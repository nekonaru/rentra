<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlokUnitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PemesananPublikController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VerifikasiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Pintasan: dari beranda langsung ke halaman status pemesanan berdasarkan kode 10 karakter.
Route::get('/cek-status', function (Request $request) {
    $kode = strtoupper((string) $request->query('kode'));
    if (preg_match('/^[A-Z0-9]{10}$/', $kode) !== 1) {
        return redirect('/')->with('status', 'Kode pemesanan harus 10 karakter huruf besar/angka.');
    }

    return redirect()->route('publik.pemesanan', $kode);
})->middleware('throttle:20,1');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding.buat');
    Route::post('/onboarding', [OnboardingController::class, 'simpan'])->name('onboarding.simpan');

    Route::middleware('rental')->group(function () {
        Route::get('/onboarding/unit', [OnboardingController::class, 'unit'])->name('onboarding.unit');
        Route::post('/onboarding/unit', [OnboardingController::class, 'simpanUnit'])->name('onboarding.unit.simpan');

        Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');
        Route::resource('unit', UnitController::class)->except(['show'])->names([
            'index' => 'unit.index',
            'create' => 'unit.create',
            'store' => 'unit.store',
            'edit' => 'unit.edit',
            'update' => 'unit.update',
            'destroy' => 'unit.destroy',
        ]);
        Route::patch('/unit/{unit}/ganti-aktif', [UnitController::class, 'gantiAktif'])->name('unit.ganti-aktif');

        Route::get('/pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
        Route::put('/pengaturan', [PengaturanController::class, 'perbarui'])->name('pengaturan.perbarui');

        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/bukti/ekspor', [VerifikasiController::class, 'eksporBukti'])->name('verifikasi.bukti.ekspor');
        Route::post('/verifikasi/{pemesanan}/terima', [VerifikasiController::class, 'terima'])->name('verifikasi.terima');
        Route::post('/verifikasi/{pemesanan}/tolak', [VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');
        Route::get('/pemesanan/{pemesanan}/bukti', [PemesananPublikController::class, 'bukti'])->name('pemesanan.bukti');

        Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::get('/pemesanan/tambah', [PemesananController::class, 'tampilTambah'])->name('pemesanan.tambah');
        Route::post('/pemesanan/tambah', [PemesananController::class, 'tambah'])->name('pemesanan.simpan-tambah');
        Route::get('/pemesanan/ekspor', [PemesananController::class, 'ekspor'])->name('pemesanan.ekspor');
        Route::get('/pemesanan/{pemesanan}/cetak', [PemesananController::class, 'cetak'])->name('pemesanan.cetak');
        Route::post('/pemesanan/{pemesanan}/serah-terima', [PemesananController::class, 'serahTerima'])->name('pemesanan.serah-terima');
        Route::post('/pemesanan/{pemesanan}/selesai', [PemesananController::class, 'selesai'])->name('pemesanan.selesai');
        Route::post('/pemesanan/{pemesanan}/batal', [PemesananController::class, 'batal'])->name('pemesanan.batal');

        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/kalender', [KalenderController::class, 'index'])->name('kalender.index');
        Route::get('/blok', [BlokUnitController::class, 'index'])->name('blok.index');
        Route::post('/blok', [BlokUnitController::class, 'simpan'])->name('blok.simpan');
        Route::delete('/blok/{blok}', [BlokUnitController::class, 'hapus'])->name('blok.hapus');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Panel admin Rentra (operator platform). Semua route di belakang auth + admin.
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/tenant', [AdminController::class, 'tenant'])->name('admin.tenant.index');
    Route::get('/admin/tenant/{rental}', [AdminController::class, 'tenantShow'])->name('admin.tenant.show');
    Route::post('/admin/tenant/{rental}/tangguhkan', [AdminController::class, 'tangguhkan'])->name('admin.tenant.tangguhkan');
    Route::post('/admin/tenant/{rental}/aktifkan', [AdminController::class, 'aktifkan'])->name('admin.tenant.aktifkan');
});

require __DIR__.'/auth.php';

// Halaman publik rental. Didaftarkan paling akhir supaya route aplikasi menang.
Route::get('/{slug}', [PublikController::class, 'tampil'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('publik.tampil');
Route::get('/{slug}/{unit}', [PublikController::class, 'unit'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->whereNumber('unit')
    ->name('publik.unit');
Route::post('/{slug}/{unit}/pratinjau-harga', [PublikController::class, 'pratinjauHarga'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->whereNumber('unit')
    ->middleware('throttle:60,1')
    ->name('publik.pratinjau-harga');
Route::post('/{slug}/{unit}/pemesanan', [PemesananPublikController::class, 'simpan'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->whereNumber('unit')
    ->middleware('throttle:10,1')
    ->name('publik.pemesanan.simpan');
Route::get('/pemesanan/{kode}', [PemesananPublikController::class, 'tampil'])
    ->where('kode', '[A-Z0-9]{10}')
    ->name('publik.pemesanan');
Route::post('/pemesanan/{kode}/bukti', [PemesananPublikController::class, 'unggahBukti'])
    ->where('kode', '[A-Z0-9]{10}')
    ->middleware('throttle:10,1')
    ->name('publik.pemesanan.bukti');
