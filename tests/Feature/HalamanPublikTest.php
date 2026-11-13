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

class HalamanPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_unit_publik_tampil_dan_unit_nonaktif_disembunyikan(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create(['nama' => 'Sony A7 III']);
        AturanTarif::factory()->for($unit)->create();
        Unit::factory()->for($rental)->create(['nama' => 'Canon Lama', 'aktif' => false]);

        $this->get('/kamera-kita')
            ->assertOk()
            ->assertSee('Sony A7 III')
            ->assertDontSee('Canon Lama');

        $this->get("/kamera-kita/{$unit->id}")
            ->assertOk()
            ->assertSee('Sony A7 III');
    }

    public function test_halaman_unit_nonaktif_mengembalikan_404(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create(['aktif' => false]);

        $this->get("/kamera-kita/{$unit->id}")->assertNotFound();
    }

    public function test_unit_dari_rental_lain_mengembalikan_404(): void
    {
        $rentalA = Rental::factory()->for(User::factory())->create(['slug' => 'rental-a']);
        $rentalB = Rental::factory()->for(User::factory())->create(['slug' => 'rental-b']);
        $unitB = Unit::factory()->for($rentalB)->create();

        $this->get("/rental-a/{$unitB->id}")->assertNotFound();
    }

    public function test_pratinjau_harga_mengembalikan_total_dari_server(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita', 'persen_dp' => 50]);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create([
            'tarif_per_hari' => 350000,
            'tarif_per_jam' => 50000,
            'minimal_sewa_jam' => 3,
            'toleransi_menit' => 15,
        ]);

        $mulai = now()->addDay()->setTime(9, 0);
        $selesai = now()->addDays(3)->setTime(9, 0);

        $response = $this->postJson('/kamera-kita/'.$unit->id.'/pratinjau-harga', [
            'mulai' => $mulai->format('Y-m-d H:i:s'),
            'selesai' => $selesai->format('Y-m-d H:i:s'),
        ]);

        $response->assertOk()->assertJsonStructure(['hari', 'sisa_jam', 'jam_part', 'total', 'dp_nominal', 'tersedia']);
        $this->assertSame(700_000.0, (float) $response->json('total'));
        $this->assertTrue($response->json('tersedia'));
    }

    public function test_pratinjau_harga_menolak_rentang_tidak_valid(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        $this->postJson('/kamera-kita/'.$unit->id.'/pratinjau-harga', [
            'mulai' => now()->addDay()->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->subHour()->format('Y-m-d H:i:s'),
        ])->assertStatus(422);
    }

    public function test_ketersediaan_mengabaikan_hold_yang_sudah_kedaluwarsa(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::Ditahan,
            'tahan_sampai' => now()->subMinute(),
            'mulai' => now()->addDay()->setTime(9, 0),
            'selesai' => now()->addDay()->setTime(21, 0),
        ]);

        $response = $this->postJson('/kamera-kita/'.$unit->id.'/pratinjau-harga', [
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(21, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($response->json('tersedia'));
    }

    public function test_ketersediaan_mendeteksi_bentrok_dengan_pemesanan_terkonfirmasi(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        Pemesanan::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'mulai' => now()->addDay()->setTime(9, 0),
            'selesai' => now()->addDay()->setTime(21, 0),
        ]);

        $response = $this->postJson('/kamera-kita/'.$unit->id.'/pratinjau-harga', [
            'mulai' => now()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(15, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertFalse($response->json('tersedia'));
    }
}
