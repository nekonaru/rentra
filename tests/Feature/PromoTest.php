<?php

namespace Tests\Feature;

use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Promo;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoTest extends TestCase
{
    use RefreshDatabase;

    private function buatRental(): Rental
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create([
            'tarif_per_hari' => 350000,
            'tarif_per_jam' => 50000,
        ]);

        return $rental;
    }

    public function test_promo_persen_mengurangi_total(): void
    {
        $rental = $this->buatRental();
        $rental->promo()->create([
            'kode' => 'HEMAT50',
            'jenis' => 'persen',
            'nilai' => 50,
            'minimal_sewa_jam' => 1,
            'kuota' => 10,
        ]);

        $this->post('/kamera-kita/1/pemesanan', [
            'nama_penyewa' => 'Penyewa Promo',
            'wa' => '628111222333',
            'mulai' => now()->addDays(5)->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDays(6)->setTime(9, 0)->format('Y-m-d H:i:s'),
            'kode_promo' => 'HEMAT50',
        ])->assertRedirect();

        $pemesanan = Pemesanan::firstOrFail();
        $this->assertSame('HEMAT50', $pemesanan->kode_promo);
        $this->assertGreaterThan(0, (float) $pemesanan->diskon_nominal);
        $this->assertSame(1, Promo::first()->terpakai);
    }

    public function test_kode_promo_tidak_valid_ditolak(): void
    {
        $this->buatRental();

        $this->post('/kamera-kita/1/pemesanan', [
            'nama_penyewa' => 'Penyewa',
            'wa' => '628111222333',
            'mulai' => now()->addDays(5)->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDays(6)->setTime(9, 0)->format('Y-m-d H:i:s'),
            'kode_promo' => 'TIDAKADA',
        ])->assertSessionHasErrors('mulai');

        $this->assertSame(0, Pemesanan::count());
    }
}
