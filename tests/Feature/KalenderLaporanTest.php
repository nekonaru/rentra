<?php

namespace Tests\Feature;

use App\Enums\StatusPemesanan;
use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KalenderLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_bisa_melihat_kalender(): void
    {
        $pemilik = User::factory()->create();
        $rental = Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();
        Pemesanan::factory()->for($unit)->terkonfirmasi()->create(['rental_id' => $rental->id]);

        $this->actingAs($pemilik)->get(route('kalender.index'))->assertOk()->assertSee('Kalender');
    }

    public function test_kalender_tidak_menampilkan_pemesanan_rental_lain(): void
    {
        $pemilikA = User::factory()->create();
        $rentalA = Rental::factory()->for($pemilikA)->create(['slug' => 'rental-a']);
        $unitA = Unit::factory()->for($rentalA)->create();
        AturanTarif::factory()->for($unitA)->create();

        $pemilikB = User::factory()->create();
        $rentalB = Rental::factory()->for($pemilikB)->create(['slug' => 'rental-b']);
        $unitB = Unit::factory()->for($rentalB)->create();
        AturanTarif::factory()->for($unitB)->create();
        Pemesanan::factory()->for($unitB)->terkonfirmasi()->create(['rental_id' => $rentalB->id, 'nama_penyewa' => 'Penyewa Rahasia']);

        $this->actingAs($pemilikA)->get(route('kalender.index'))->assertOk()->assertDontSee('Penyewa Rahasia');
    }

    public function test_pemilik_bisa_melihat_halaman_laporan(): void
    {
        $pemilik = User::factory()->create();
        $rental = Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();
        Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::Selesai,
            'mulai' => now()->subDays(5),
            'selesai' => now()->subDays(3),
        ]);

        $this->actingAs($pemilik)->get(route('laporan.index'))->assertOk()->assertSee('Laporan');
    }
}
