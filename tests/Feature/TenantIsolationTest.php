<?php

namespace Tests\Feature;

use App\Models\PriceRule;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_cannot_edit_another_rentals_unit(): void
    {
        $ownerA = User::factory()->create();
        $rentalA = Rental::factory()->for($ownerA)->create();
        $unitA = Unit::factory()->for($rentalA)->create();

        $ownerB = User::factory()->create();
        Rental::factory()->for($ownerB)->create();

        $this->actingAs($ownerB)
            ->get(route('units.edit', $unitA))
            ->assertForbidden();

        $this->actingAs($ownerB)
            ->put(route('units.update', $unitA), [
                'nama' => 'Bajakan',
                'tarif_per_hari' => 1000,
                'tarif_per_jam' => 1000,
                'minimal_sewa_jam' => 1,
                'toleransi_menit' => 15,
            ])
            ->assertForbidden();

        $this->assertNotSame('Bajakan', $unitA->fresh()->nama);
    }

    public function test_owner_cannot_delete_another_rentals_unit(): void
    {
        $ownerA = User::factory()->create();
        $rentalA = Rental::factory()->for($ownerA)->create();
        $unitA = Unit::factory()->for($rentalA)->create();
        PriceRule::factory()->for($unitA)->create();

        $ownerB = User::factory()->create();
        Rental::factory()->for($ownerB)->create();

        $this->actingAs($ownerB)
            ->delete(route('units.destroy', $unitA))
            ->assertForbidden();

        $this->assertDatabaseHas('units', ['id' => $unitA->id]);
    }

    public function test_unit_index_only_shows_own_units(): void
    {
        $ownerA = User::factory()->create();
        $rentalA = Rental::factory()->for($ownerA)->create();
        Unit::factory()->for($rentalA)->create(['nama' => 'Unit Milik A']);

        $ownerB = User::factory()->create();
        $rentalB = Rental::factory()->for($ownerB)->create();
        Unit::factory()->for($rentalB)->create(['nama' => 'Unit Milik B']);

        $this->actingAs($ownerB)
            ->get(route('units.index'))
            ->assertOk()
            ->assertSee('Unit Milik B')
            ->assertDontSee('Unit Milik A');
    }
}
