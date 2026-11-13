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

class PemesananManualTest extends TestCase
{
    use RefreshDatabase;

    private function buatUnit(?Rental $rental = null): array
    {
        $rental ??= Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        return [$rental, $unit];
    }

    public function test_pemilik_bisa_membuat_pemesanan_manual_terkonfirmasi(): void
    {
        $pemilik = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($pemilik)->create(['slug' => 'milik-saya']));

        $response = $this->actingAs($pemilik)->post(route('pemesanan.simpan-tambah'), [
            'unit_id' => $unit->id,
            'nama_penyewa' => 'Pelanggan Jalanan',
            'wa' => '628333444555',
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(17, 0)->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('pemesanan.index'));
        $pemesanan = Pemesanan::firstOrFail();
        $this->assertSame(StatusPemesanan::Terkonfirmasi, $pemesanan->status);
        $this->assertNull($pemesanan->tahan_sampai);
    }

    public function test_pemilik_tidak_bisa_memesan_unit_rental_orang_lain(): void
    {
        $pemilikA = User::factory()->create();
        [$rentalA, $unitA] = $this->buatUnit(Rental::factory()->for($pemilikA)->create(['slug' => 'rental-a']));
        $pemilikB = User::factory()->create();
        Rental::factory()->for($pemilikB)->create(['slug' => 'rental-b']);

        $this->actingAs($pemilikB)->post(route('pemesanan.simpan-tambah'), [
            'unit_id' => $unitA->id,
            'nama_penyewa' => 'Orang Lain',
            'wa' => '628333444555',
            'mulai' => now()->addDay()->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->addHours(6)->format('Y-m-d H:i:s'),
        ])->assertNotFound();

        $this->assertSame(0, Pemesanan::count());
    }

    public function test_cetak_struk_pemilik_sendiri_bukan_milik_orang(): void
    {
        $pemilikA = User::factory()->create();
        [$rentalA, $unitA] = $this->buatUnit(Rental::factory()->for($pemilikA)->create(['slug' => 'rental-a']));
        $pemilikB = User::factory()->create();
        Rental::factory()->for($pemilikB)->create(['slug' => 'rental-b']);

        $pemesanan = Pemesanan::factory()->for($unitA)->terkonfirmasi()->create(['rental_id' => $rentalA->id]);

        $this->actingAs($pemilikA)->get(route('pemesanan.cetak', $pemesanan))->assertOk()->assertSee($pemesanan->kode);
        $this->actingAs($pemilikB)->get(route('pemesanan.cetak', $pemesanan))->assertForbidden();
    }

    public function test_dashboard_menampilkan_agenda_hari_ini(): void
    {
        $pemilik = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']));

        Pemesanan::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'mulai' => now()->subHours(2),
            'selesai' => now()->addHours(6),
        ]);

        $this->actingAs($pemilik)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Agenda hari ini');
    }
}
