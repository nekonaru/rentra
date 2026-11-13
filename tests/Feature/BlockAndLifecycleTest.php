<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\PriceRule;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\UnitBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockAndLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function buatUnit(?Rental $rental = null): array
    {
        $rental ??= Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        PriceRule::factory()->for($unit)->create();

        return [$rental, $unit];
    }

    public function test_block_prevents_booking(): void
    {
        $owner = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($owner)->create(['slug' => 'kamera-kita']));

        UnitBlock::factory()->for($unit)->create([
            'mulai' => now()->addDay()->setTime(0, 0),
            'selesai' => now()->addDay()->setTime(23, 59),
        ]);

        $this->post("/{$rental->slug}/{$unit->id}/booking", [
            'nama_penyewa' => 'Budi',
            'wa' => '6281234567890',
            'mulai' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('mulai');

        $this->assertSame(0, Booking::count());
    }

    public function test_block_conflicting_with_active_booking_requires_paksa(): void
    {
        $owner = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($owner)->create(['slug' => 'kamera-kita']));

        Booking::factory()->for($unit)->terkonfirmasi()->create([
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

        $this->actingAs($owner)->post(route('blok.store'), $payload)->assertSessionHas('konflik');
        $this->assertSame(0, UnitBlock::count());

        $this->actingAs($owner)->post(route('blok.store'), $payload + ['paksa' => '1'])->assertRedirect();
        $this->assertSame(1, UnitBlock::count());
    }

    public function test_owner_cannot_block_another_rentals_unit(): void
    {
        $ownerA = User::factory()->create();
        [$rentalA, $unitA] = $this->buatUnit(Rental::factory()->for($ownerA)->create(['slug' => 'rental-a']));
        $ownerB = User::factory()->create();
        Rental::factory()->for($ownerB)->create(['slug' => 'rental-b']);

        $this->actingAs($ownerB)->post(route('blok.store'), [
            'unit_id' => $unitA->id,
            'mulai' => now()->addDay()->format('Y-m-d H:i:s'),
            'selesai' => now()->addDay()->addHours(4)->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('unit_id');

        $this->assertSame(0, UnitBlock::count());
    }

    public function test_serah_terima_and_selesai_flow(): void
    {
        $owner = User::factory()->create();
        [$rental, $unit] = $this->buatUnit(Rental::factory()->for($owner)->create(['slug' => 'kamera-kita']));

        $booking = Booking::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
        ]);

        $this->actingAs($owner)->post(route('bookings.serah-terima', $booking));
        $this->assertSame(BookingStatus::SedangDisewa, $booking->fresh()->status);

        $this->actingAs($owner)->post(route('bookings.selesai', $booking));
        $this->assertSame(BookingStatus::Selesai, $booking->fresh()->status);
    }

    public function test_other_owner_cannot_serah_terima(): void
    {
        $ownerA = User::factory()->create();
        [$rentalA, $unitA] = $this->buatUnit(Rental::factory()->for($ownerA)->create(['slug' => 'rental-a']));
        $ownerB = User::factory()->create();
        Rental::factory()->for($ownerB)->create(['slug' => 'rental-b']);

        $booking = Booking::factory()->for($unitA)->terkonfirmasi()->create(['rental_id' => $rentalA->id]);

        $this->actingAs($ownerB)->post(route('bookings.serah-terima', $booking))->assertForbidden();
        $this->assertSame(BookingStatus::Terkonfirmasi, $booking->fresh()->status);
    }
}
