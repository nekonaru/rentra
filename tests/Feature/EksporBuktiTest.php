<?php

namespace Tests\Feature;

use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EksporBuktiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_bisa_mengunduh_zip_bukti(): void
    {
        Storage::fake('local');
        $pemilik = User::factory()->create();
        $rental = Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        Storage::disk('local')->put('bukti/test-1.png', 'isi');
        Storage::disk('local')->put('bukti/test-2.png', 'isi2');

        Pemesanan::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'jalur_bukti' => 'bukti/test-1.png',
        ]);
        Pemesanan::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'jalur_bukti' => 'bukti/test-2.png',
        ]);

        $response = $this->actingAs($pemilik)->get(route('verifikasi.bukti.ekspor'));

        $response->assertOk();
        $this->assertStringContainsString('bukti-dp', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.zip', $response->headers->get('Content-Disposition'));
    }

    public function test_pemilik_tanpa_bukti_mendapat_404(): void
    {
        $pemilikA = User::factory()->create();
        $rentalA = Rental::factory()->for($pemilikA)->create(['slug' => 'rental-a']);
        $unitA = Unit::factory()->for($rentalA)->create();
        AturanTarif::factory()->for($unitA)->create();
        Pemesanan::factory()->for($unitA)->terkonfirmasi()->create(['rental_id' => $rentalA->id, 'jalur_bukti' => 'bukti/test.png']);

        $pemilikB = User::factory()->create();
        Rental::factory()->for($pemilikB)->create(['slug' => 'rental-b']);

        $this->actingAs($pemilikB)->get(route('verifikasi.bukti.ekspor'))->assertNotFound();
    }
}
