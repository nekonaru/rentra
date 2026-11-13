<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\PriceRule;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_unit_page_renders_and_inactive_units_are_hidden(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create(['nama' => 'Sony A7 III']);
        PriceRule::factory()->for($unit)->create();
        $inactive = Unit::factory()->for($rental)->create(['nama' => 'Canon Lama', 'aktif' => false]);

        $this->get('/kamera-kita')
            ->assertOk()
            ->assertSee('Sony A7 III')
            ->assertDontSee('Canon Lama');

        $this->get("/kamera-kita/{$unit->id}")
            ->assertOk()
            ->assertSee('Sony A7 III');
    }

    public function test_inactive_unit_page_returns_404(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create(['aktif' => false]);

        $this->get("/kamera-kita/{$unit->id}")->assertNotFound();
    }

    public function test_unit_from_other_rental_returns_404(): void
    {
        $rentalA = Rental::factory()->for(User::factory())->create(['slug' => 'rental-a']);
        $rentalB = Rental::factory()->for(User::factory())->create(['slug' => 'rental-b']);
        $unitB = Unit::factory()->for($rentalB)->create();

        $this->get("/rental-a/{$unitB->id}")->assertNotFound();
    }

    public function test_price_preview_returns_server_computed_total(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita', 'persen_dp' => 50]);
        $unit = Unit::factory()->for($rental)->create();
        PriceRule::factory()->for($unit)->create([
            'tarif_per_hari' => 350000,
            'tarif_per_jam' => 50000,
            'minimal_sewa_jam' => 3,
            'toleransi_menit' => 15,
        ]);

        $response = $this->postJson('/kamera-kita/'.$unit->id.'/harga-preview', [
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDays(3)->setTime(9, 0)->format('Y-m-d H:i:s'),
        ]);

        $response->assertOk()->assertJsonStructure(['hari', 'sisa_jam', 'jam_part', 'total', 'dp_nominal', 'tersedia']);
        $this->assertSame(700_000.0, (float) $response->json('total'));
        $this->assertTrue($response->json('tersedia'));
    }

    public function test_price_preview_rejects_invalid_range(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        PriceRule::factory()->for($unit)->create();

        $this->postJson('/kamera-kita/'.$unit->id.'/harga-preview', [
            'mulai' => now()->addDay()->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->subHour()->format('Y-m-d H:i:s'),
        ])->assertStatus(422);
    }

    public function test_availability_ignores_expired_holds(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        PriceRule::factory()->for($unit)->create();

        Booking::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => BookingStatus::Ditahan,
            'hold_expires_at' => now()->subMinute(),
            'mulai' => now()->addDay()->setTime(9, 0),
            'selesai' => now()->addDay()->setTime(21, 0),
        ]);

        $response = $this->postJson('/kamera-kita/'.$unit->id.'/harga-preview', [
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(21, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($response->json('tersedia'));
    }

    public function test_availability_detects_clash_with_confirmed_booking(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        PriceRule::factory()->for($unit)->create();

        Booking::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'mulai' => now()->addDay()->setTime(9, 0),
            'selesai' => now()->addDay()->setTime(21, 0),
        ]);

        $response = $this->postJson('/kamera-kita/'.$unit->id.'/harga-preview', [
            'mulai' => now()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(15, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertFalse($response->json('tersedia'));
    }
}
