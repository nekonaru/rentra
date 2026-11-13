<?php

namespace Tests\Feature;

use App\Enums\StatusPemesanan;
use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PemesananTest extends TestCase
{
    use RefreshDatabase;

    private function buatUnit(?Rental $rental = null): array
    {
        $rental ??= Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita', 'durasi_tahan_menit' => 45, 'persen_dp' => 50]);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create([
            'tarif_per_hari' => 350000,
            'tarif_per_jam' => 50000,
            'minimal_sewa_jam' => 3,
            'toleransi_menit' => 15,
        ]);

        return [$rental, $unit];
    }

    public function test_guest_bisa_memesan_unit_yang_aktif(): void
    {
        [$rental, $unit] = $this->buatUnit();

        $mulai = now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s');
        $selesai = now()->addDays(3)->setTime(9, 0)->format('Y-m-d H:i:s');

        $response = $this->post("/{$rental->slug}/{$unit->id}/pemesanan", [
            'nama_penyewa' => 'Budi Penyewa',
            'wa' => '6281234567890',
            'mulai' => $mulai,
            'selesai' => $selesai,
        ]);

        $pemesanan = Pemesanan::firstOrFail();
        $response->assertRedirect(route('publik.pemesanan', $pemesanan->kode));
        $this->assertSame(StatusPemesanan::Ditahan, $pemesanan->status);
        $this->assertEqualsWithDelta(now()->addMinutes(45)->timestamp, $pemesanan->tahan_sampai->timestamp, 5);
        // 48 jam - toleransi 15 menit = tagihan mentok di 2 tarif harian.
        $this->assertSame('700000.00', $pemesanan->total);
    }

    public function test_submit_ganda_slot_sama_hanya_satu_yang_lolos(): void
    {
        [$rental, $unit] = $this->buatUnit();

        $mulai = now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s');
        $selesai = now()->addDay()->setTime(17, 0)->format('Y-m-d H:i:s');

        $payload = ['nama_penyewa' => 'Budi', 'wa' => '628111111111', 'mulai' => $mulai, 'selesai' => $selesai];

        $this->post("/{$rental->slug}/{$unit->id}/pemesanan", $payload)->assertRedirect();
        $this->post("/{$rental->slug}/{$unit->id}/pemesanan", $payload + ['nama_penyewa' => 'Ani'])
            ->assertSessionHasErrors('mulai');

        $this->assertSame(1, Pemesanan::count());
    }

    public function test_hold_kedaluwarsa_tidak_menghalangi_slot(): void
    {
        [$rental, $unit] = $this->buatUnit();

        Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::Ditahan,
            'tahan_sampai' => now()->subMinute(),
            'mulai' => now()->addDay()->setTime(9, 0),
            'selesai' => now()->addDay()->setTime(17, 0),
        ]);

        $this->post("/{$rental->slug}/{$unit->id}/pemesanan", [
            'nama_penyewa' => 'Budi',
            'wa' => '6281234567890',
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(17, 0)->format('Y-m-d H:i:s'),
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame(2, Pemesanan::count());
    }

    public function test_unggah_bukti_masuk_ke_menunggu_verifikasi_dan_disimpan_privat(): void
    {
        Storage::fake('local');
        [$rental, $unit] = $this->buatUnit();

        $pemesanan = Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::Ditahan,
            'tahan_sampai' => now()->addMinutes(30),
        ]);

        $this->post("/pemesanan/{$pemesanan->kode}/bukti", [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect(route('publik.pemesanan', $pemesanan->kode));

        $pemesanan->refresh();
        $this->assertSame(StatusPemesanan::MenungguVerifikasi, $pemesanan->status);
        $this->assertNotNull($pemesanan->jalur_bukti);
        Storage::disk('local')->assertExists($pemesanan->jalur_bukti);
    }

    public function test_unggah_menolak_file_bukan_gambar_atau_pdf(): void
    {
        Storage::fake('local');
        [$rental, $unit] = $this->buatUnit();

        $pemesanan = Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::Ditahan,
            'tahan_sampai' => now()->addMinutes(30),
        ]);

        $this->post("/pemesanan/{$pemesanan->kode}/bukti", [
            'bukti' => UploadedFile::fake()->create('virus.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors('bukti');

        $this->assertSame(StatusPemesanan::Ditahan, $pemesanan->fresh()->status);
    }

    public function test_pemilik_bisa_terima_dan_tolak_dengan_alasan(): void
    {
        $pemilik = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($pemilik)->create(['slug' => 'milik-saya', 'durasi_tahan_menit' => 30]));

        $pemesanan = Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::MenungguVerifikasi,
        ]);

        $this->actingAs($pemilik)
            ->post(route('verifikasi.tolak', $pemesanan), ['catatan_tolak' => 'Nominal tidak sesuai'])
            ->assertSessionHasNoErrors();

        $pemesanan->refresh();
        $this->assertSame(StatusPemesanan::Ditahan, $pemesanan->status);
        $this->assertSame('Nominal tidak sesuai', $pemesanan->catatan_tolak);

        $pemesanan->update(['status' => StatusPemesanan::MenungguVerifikasi, 'catatan_tolak' => null]);

        $this->actingAs($pemilik)
            ->post(route('verifikasi.terima', $pemesanan));

        $this->assertSame(StatusPemesanan::Terkonfirmasi, $pemesanan->fresh()->status);
    }

    public function test_pemilik_lain_tidak_bisa_terima_atau_mengunduh_bukti(): void
    {
        Storage::fake('local');
        $pemilikA = User::factory()->create();
        [$rentalA, $unitA] = $this->buatUnit(Rental::factory()->for($pemilikA)->create(['slug' => 'rental-a']));
        $pemilikB = User::factory()->create();
        Rental::factory()->for($pemilikB)->create(['slug' => 'rental-b']);

        $pemesanan = Pemesanan::factory()->for($unitA)->create([
            'rental_id' => $rentalA->id,
            'status' => StatusPemesanan::MenungguVerifikasi,
            'jalur_bukti' => 'bukti/test.jpg',
        ]);
        Storage::disk('local')->put('bukti/test.jpg', 'isi-palsu');

        $this->actingAs($pemilikB)->post(route('verifikasi.terima', $pemesanan))->assertForbidden();
        $this->actingAs($pemilikB)->get(route('pemesanan.bukti', $pemesanan))->assertForbidden();

        $this->assertSame(StatusPemesanan::MenungguVerifikasi, $pemesanan->fresh()->status);
    }

    public function test_perintah_sapu_menandai_hold_kedaluwarsa_dan_keterlambatan(): void
    {
        [$rental, $unit] = $this->buatUnit();

        $kedaluwarsa = Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::Ditahan,
            'tahan_sampai' => now()->subMinute(),
        ]);

        $terlambat = Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::SedangDisewa,
            'mulai' => now()->subDays(3),
            'selesai' => now()->subDay(),
            'tahan_sampai' => null,
        ]);

        $this->artisan('pemesanan:sapu')->assertSuccessful();

        $this->assertSame(StatusPemesanan::Kedaluwarsa, $kedaluwarsa->fresh()->status);
        $this->assertSame(StatusPemesanan::Terlambat, $terlambat->fresh()->status);
    }
}
