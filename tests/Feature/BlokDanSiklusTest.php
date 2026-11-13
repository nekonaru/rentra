<?php

namespace Tests\Feature;

use App\Enums\StatusPemesanan;
use App\Models\AturanTarif;
use App\Models\BlokUnit;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlokDanSiklusTest extends TestCase
{
    use RefreshDatabase;

    private function buatUnit(?Rental $rental = null): array
    {
        $rental ??= Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        return [$rental, $unit];
    }

    public function test_blok_menghalangi_pemesanan(): void
    {
        $pemilik = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']));

        BlokUnit::factory()->for($unit)->create([
            'mulai' => now()->addDay()->setTime(0, 0),
            'selesai' => now()->addDay()->setTime(23, 59),
        ]);

        $this->post("/{$rental->slug}/{$unit->id}/pemesanan", [
            'nama_penyewa' => 'Budi',
            'wa' => '6281234567890',
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('mulai');

        $this->assertSame(0, Pemesanan::count());
    }

    public function test_blok_yang_bentrok_dengan_pemesanan_aktif_wajib_centang_paksa(): void
    {
        $pemilik = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']));

        Pemesanan::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'mulai' => now()->addDay()->setTime(9, 0),
            'selesai' => now()->addDays(2)->setTime(9, 0),
        ]);

        $payload = [
            'unit_id' => $unit->id,
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(17, 0)->format('Y-m-d H:i:s'),
            'alasan' => 'Servis',
        ];

        $this->actingAs($pemilik)->post(route('blok.simpan'), $payload)->assertSessionHas('konflik');
        $this->assertSame(0, BlokUnit::count());

        $this->actingAs($pemilik)->post(route('blok.simpan'), $payload + ['paksa' => '1'])->assertRedirect();
        $this->assertSame(1, BlokUnit::count());
    }

    public function test_pemilik_tidak_bisa_memblokir_unit_rental_orang_lain(): void
    {
        $pemilikA = User::factory()->create();
        [$rentalA, $unitA] = $this->buatUnit(Rental::factory()->for($pemilikA)->create(['slug' => 'rental-a']));
        $pemilikB = User::factory()->create();
        Rental::factory()->for($pemilikB)->create(['slug' => 'rental-b']);

        $this->actingAs($pemilikB)->post(route('blok.simpan'), [
            'unit_id' => $unitA->id,
            'mulai' => now()->addDay()->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->addHours(4)->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('unit_id');

        $this->assertSame(0, BlokUnit::count());
    }

    public function test_serah_terima_dan_selesai(): void
    {
        $pemilik = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']));

        $pemesanan = Pemesanan::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
        ]);

        $this->actingAs($pemilik)->post(route('pemesanan.serah-terima', $pemesanan));
        $this->assertSame(StatusPemesanan::SedangDisewa, $pemesanan->fresh()->status);

        $this->actingAs($pemilik)->post(route('pemesanan.selesai', $pemesanan));
        $this->assertSame(StatusPemesanan::Selesai, $pemesanan->fresh()->status);
    }

    public function test_pemilik_lain_tidak_bisa_serah_terima(): void
    {
        $pemilikA = User::factory()->create();
        [$rentalA, $unitA] = $this->buatUnit(Rental::factory()->for($pemilikA)->create(['slug' => 'rental-a']));
        $pemilikB = User::factory()->create();
        Rental::factory()->for($pemilikB)->create(['slug' => 'rental-b']);

        $pemesanan = Pemesanan::factory()->for($unitA)->terkonfirmasi()->create(['rental_id' => $rentalA->id]);

        $this->actingAs($pemilikB)->post(route('pemesanan.serah-terima', $pemesanan))->assertForbidden();
        $this->assertSame(StatusPemesanan::Terkonfirmasi, $pemesanan->fresh()->status);
    }
}
