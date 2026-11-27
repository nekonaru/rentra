<?php

namespace Tests\Feature;

use App\Enums\StatusPemesanan;
use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Ulasan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiturPublikTest extends TestCase
{
    use RefreshDatabase;

    private function buatPemesananSelesai(): Pemesanan
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        return Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::Selesai,
        ]);
    }

    public function test_pemesanan_selesai_bisa_diulas(): void
    {
        $pemesanan = $this->buatPemesananSelesai();

        $this->post(route('publik.pemesanan.ulasan', $pemesanan->kode), [
            'rating' => 5,
            'komentar' => 'Unit bagus, pemilik ramah dan tepat waktu.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ulasan', [
            'pemesanan_id' => $pemesanan->id,
            'rating' => 5,
            'status' => 'menunggu',
        ]);
    }

    public function test_pemesanan_belum_selesai_tidak_bisa_diulas(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();
        $pemesanan = Pemesanan::factory()->for($unit)->create(['rental_id' => $rental->id]);

        $this->post(route('publik.pemesanan.ulasan', $pemesanan->kode), [
            'rating' => 5,
            'komentar' => 'Komentaar yang cukup panjang.',
        ])->assertStatus(422);
    }

    public function test_unduh_kalender_ics(): void
    {
        $pemesanan = $this->buatPemesananSelesai();

        $response = $this->get(route('publik.pemesanan.kalender', $pemesanan->kode));

        $response->assertOk();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('BEGIN:VEVENT', $response->getContent());
        $this->assertStringContainsString($pemesanan->kode, $response->getContent());
    }

    public function test_ulasan_hanya_tampil_setelah_disetujui(): void
    {
        $pemesanan = $this->buatPemesananSelesai();
        $unit = $pemesanan->unit;

        Ulasan::create([
            'pemesanan_id' => $pemesanan->id,
            'unit_id' => $unit->id,
            'rental_id' => $pemesanan->rental_id,
            'rating' => 5,
            'komentar' => 'Ulasan yang belum disetujui.',
            'status' => 'menunggu',
        ]);

        $this->get(route('publik.unit', [$pemesanan->rental->slug, $unit->id]))
            ->assertOk()
            ->assertDontSee('Ulasan yang belum disetujui.');

        Ulasan::where('pemesanan_id', $pemesanan->id)->update(['status' => 'tampil']);

        $this->get(route('publik.unit', [$pemesanan->rental->slug, $unit->id]))
            ->assertOk()
            ->assertSee('Ulasan yang belum disetujui.');
    }
}
