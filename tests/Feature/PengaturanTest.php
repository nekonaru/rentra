<?php

namespace Tests\Feature;

use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_bisa_melihat_dan_mengubah_pengaturan(): void
    {
        $pemilik = User::factory()->create();
        Rental::factory()->for($pemilik)->create(['slug' => 'milik-saya']);

        $this->actingAs($pemilik)->get(route('pengaturan.edit'))->assertOk()->assertSee('milik-saya');

        $this->actingAs($pemilik)->put(route('pengaturan.perbarui'), [
            'nama' => 'Rental Baru',
            'no_wa' => '628777111222',
            'info_rekening' => 'BRI 998877 a.n. Pemilik',
            'durasi_tahan_menit' => 90,
            'persen_dp' => 30,
        ])->assertRedirect(route('pengaturan.edit'));

        $rental = $pemilik->rental->fresh();
        $this->assertSame('Rental Baru', $rental->nama);
        $this->assertSame(90, $rental->durasi_tahan_menit);
        $this->assertSame('milik-saya', $rental->slug); // slug tidak bisa diubah
    }

    public function test_ekspor_csv_hanya_berisi_rental_sendiri(): void
    {
        $pemilikA = User::factory()->create();
        $rentalA = Rental::factory()->for($pemilikA)->create(['slug' => 'rental-a']);
        $unitA = Unit::factory()->for($rentalA)->create();
        AturanTarif::factory()->for($unitA)->create();
        Pemesanan::factory()->for($unitA)->terkonfirmasi()->create([
            'rental_id' => $rentalA->id,
            'kode' => 'KODEMILIKA',
        ]);

        $pemilikB = User::factory()->create();
        $rentalB = Rental::factory()->for($pemilikB)->create(['slug' => 'rental-b']);
        $unitB = Unit::factory()->for($rentalB)->create();
        AturanTarif::factory()->for($unitB)->create();
        Pemesanan::factory()->for($unitB)->create(['rental_id' => $rentalB->id, 'kode' => 'KODEMILIKB']);

        $response = $this->actingAs($pemilikA)->get(route('pemesanan.ekspor'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $isi = $response->streamedContent();
        $this->assertStringContainsString('KODEMILIKA', $isi);
        $this->assertStringNotContainsString('KODEMILIKB', $isi);
    }
}
