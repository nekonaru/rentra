<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnitController;
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
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
