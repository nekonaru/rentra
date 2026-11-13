<?php

use App\Http\Controllers\BookingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\UnitBlockController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VerifikasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::middleware('rental')->group(function () {
        Route::get('/onboarding/unit', [OnboardingController::class, 'unit'])->name('onboarding.unit');
        Route::post('/onboarding/unit', [OnboardingController::class, 'storeUnit'])->name('onboarding.unit.store');

        Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');
        Route::resource('units', UnitController::class)->except(['show']);
        Route::patch('/units/{unit}/toggle-aktif', [UnitController::class, 'toggleAktif'])->name('units.toggle-aktif');

        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('/verifikasi/{booking}/terima', [VerifikasiController::class, 'terima'])->name('verifikasi.terima');
        Route::post('/verifikasi/{booking}/tolak', [VerifikasiController::class, 'tolak'])->name('verifikasi.tolak');
        Route::get('/booking/{booking}/bukti', [PublicBookingController::class, 'bukti'])->name('bookings.bukti');

        Route::get('/bookings', [BookingsController::class, 'index'])->name('bookings.index');
        Route::post('/bookings/{booking}/serah-terima', [BookingsController::class, 'serahTerima'])->name('bookings.serah-terima');
        Route::post('/bookings/{booking}/selesai', [BookingsController::class, 'selesai'])->name('bookings.selesai');
        Route::post('/bookings/{booking}/batal', [BookingsController::class, 'batal'])->name('bookings.batal');

        Route::get('/blok', [UnitBlockController::class, 'index'])->name('blok.index');
        Route::post('/blok', [UnitBlockController::class, 'store'])->name('blok.store');
        Route::delete('/blok/{unitBlock}', [UnitBlockController::class, 'destroy'])->name('blok.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Halaman publik rental. Didaftarkan paling akhir supaya route aplikasi menang.
Route::get('/{slug}', [PublicController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('public.show');
Route::get('/{slug}/{unit}', [PublicController::class, 'unit'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->whereNumber('unit')
    ->name('public.unit');
Route::post('/{slug}/{unit}/harga-preview', [PublicController::class, 'preview'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->whereNumber('unit')
    ->middleware('throttle:60,1')
    ->name('public.preview');
Route::post('/{slug}/{unit}/booking', [PublicBookingController::class, 'store'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->whereNumber('unit')
    ->middleware('throttle:10,1')
    ->name('public.booking.store');
Route::get('/booking/{kode}', [PublicBookingController::class, 'show'])
    ->where('kode', '[A-Za-z0-9]+')
    ->name('public.booking');
Route::post('/booking/{kode}/bukti', [PublicBookingController::class, 'uploadBukti'])
    ->where('kode', '[A-Za-z0-9]+')
    ->middleware('throttle:10,1')
    ->name('public.booking.bukti');
