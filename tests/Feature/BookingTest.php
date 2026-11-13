<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\PriceRule;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function buatUnit(?Rental $rental = null): array
    {
        $rental ??= Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita', 'durasi_hold_menit' => 45, 'persen_dp' => 50]);
        $unit = Unit::factory()->for($rental)->create();
        PriceRule::factory()->for($unit)->create([
            'tarif_per_hari' => 350000,
            'tarif_per_jam' => 50000,
            'minimal_sewa_jam' => 3,
            'toleransi_menit' => 15,
        ]);

        return [$rental, $unit];
    }

    public function test_guest_can_book_active_unit(): void
    {
        [$rental, $unit] = $this->buatUnit();

        $mulai = now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s');
        $selesai = now()->addDays(3)->setTime(9, 0)->format('Y-m-d H:i:s');

        $response = $this->post("/{$rental->slug}/{$unit->id}/booking", [
            'nama_penyewa' => 'Budi Penyewa',
            'wa' => '6281234567890',
            'mulai' => $mulai,
            'selesai' => $selesai,
        ]);

        $booking = Booking::firstOrFail();
        $response->assertRedirect(route('public.booking', $booking->kode));
        $this->assertSame(BookingStatus::Ditahan, $booking->status);
        $this->assertEqualsWithDelta(now()->addMinutes(45)->timestamp, $booking->hold_expires_at->timestamp, 5);
        // 25 jam - 15 menit toleransi = billed, capped by the day rate.
        $this->assertSame('700000.00', $booking->total);
    }

    public function test_double_submit_same_slot_only_one_wins(): void
    {
        [$rental, $unit] = $this->buatUnit();

        $mulai = now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s');
        $selesai = now()->addDay()->setTime(17, 0)->format('Y-m-d H:i:s');

        $payload = ['nama_penyewa' => 'Budi', 'wa' => '628111111111', 'mulai' => $mulai, 'selesai' => $selesai];

        $this->post("/{$rental->slug}/{$unit->id}/booking", $payload)->assertRedirect();
        $this->post("/{$rental->slug}/{$unit->id}/booking", array_merge($payload, ['nama_penyewa' => 'Ani']))
            ->assertSessionHasErrors('mulai');

        $this->assertSame(1, Booking::count());
    }

    public function test_expired_hold_does_not_block_slot(): void
    {
        [$rental, $unit] = $this->buatUnit();

        Booking::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => BookingStatus::Ditahan,
            'hold_expires_at' => now()->subMinute(),
            'mulai' => now()->addDay()->setTime(9, 0),
            'selesai' => now()->addDay()->setTime(17, 0),
        ]);

        $this->post("/{$rental->slug}/{$unit->id}/booking", [
            'nama_penyewa' => 'Budi',
            'wa' => '6281234567890',
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(17, 0)->format('Y-m-d H:i:s'),
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame(2, Booking::count());
    }

    public function test_upload_bukti_moves_to_menunggu_verifikasi_and_stores_privately(): void
    {
        Storage::fake('local');
        [$rental, $unit] = $this->buatUnit();

        $booking = Booking::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => BookingStatus::Ditahan,
            'hold_expires_at' => now()->addMinutes(30),
        ]);

        $this->post("/booking/{$booking->kode}/bukti", [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect(route('public.booking', $booking->kode));

        $booking->refresh();
        $this->assertSame(BookingStatus::MenungguVerifikasi, $booking->status);
        $this->assertNotNull($booking->bukti_path);
        Storage::disk('local')->assertExists($booking->bukti_path);
    }

    public function test_upload_rejects_non_image_or_pdf(): void
    {
        Storage::fake('local');
        [$rental, $unit] = $this->buatUnit();

        $booking = Booking::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => BookingStatus::Ditahan,
            'hold_expires_at' => now()->addMinutes(30),
        ]);

        $this->post("/booking/{$booking->kode}/bukti", [
            'bukti' => UploadedFile::fake()->create('virus.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors('bukti');

        $this->assertSame(BookingStatus::Ditahan, $booking->fresh()->status);
    }

    public function test_owner_can_terima_and_reject_with_note(): void
    {
        $owner = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($owner)->create(['slug' => 'milik-saya', 'durasi_hold_menit' => 30]));

        $booking = Booking::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => BookingStatus::MenungguVerifikasi,
        ]);

        $this->actingAs($owner)
            ->post(route('verifikasi.tolak', $booking), ['catatan_tolak' => 'Nominal tidak sesuai'])
            ->assertSessionHasNoErrors();

        $booking->refresh();
        $this->assertSame(BookingStatus::Ditahan, $booking->status);
        $this->assertSame('Nominal tidak sesuai', $booking->catatan_tolak);

        $booking->update(['status' => BookingStatus::MenungguVerifikasi, 'catatan_tolak' => null]);

        $this->actingAs($owner)
            ->post(route('verifikasi.terima', $booking));

        $this->assertSame(BookingStatus::Terkonfirmasi, $booking->fresh()->status);
    }

    public function test_other_owner_cannot_terima_or_download_bukti(): void
    {
        Storage::fake('local');
        $ownerA = User::factory()->create();
        [$rentalA, $unitA] = $this->buatUnit(Rental::factory()->for($ownerA)->create(['slug' => 'rental-a']));
        $ownerB = User::factory()->create();
        Rental::factory()->for($ownerB)->create(['slug' => 'rental-b']);

        $booking = Booking::factory()->for($unitA)->create([
            'rental_id' => $rentalA->id,
            'status' => BookingStatus::MenungguVerifikasi,
            'bukti_path' => 'bukti/test.jpg',
        ]);
        Storage::disk('local')->put('bukti/test.jpg', 'fake-image-content');

        $this->actingAs($ownerB)->post(route('verifikasi.terima', $booking))->assertForbidden();
        $this->actingAs($ownerB)->get(route('bookings.bukti', $booking))->assertForbidden();

        $this->assertSame(BookingStatus::MenungguVerifikasi, $booking->fresh()->status);
    }

    public function test_sweep_marks_expired_holds_and_late_rentals(): void
    {
        [$rental, $unit] = $this->buatUnit();

        $expired = Booking::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => BookingStatus::Ditahan,
            'hold_expires_at' => now()->subMinute(),
        ]);

        $terlambat = Booking::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => BookingStatus::SedangDisewa,
            'mulai' => now()->subDays(3),
            'selesai' => now()->subDay(),
            'hold_expires_at' => null,
        ]);

        $this->artisan('bookings:sweep')->assertSuccessful();

        $this->assertSame(BookingStatus::Kedaluwarsa, $expired->fresh()->status);
        $this->assertSame(BookingStatus::Terlambat, $terlambat->fresh()->status);
    }
}
