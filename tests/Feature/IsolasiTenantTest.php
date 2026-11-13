<?php

namespace Tests\Feature;

use App\Models\AturanTarif;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsolasiTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_tidak_bisa_mengubah_unit_rental_orang_lain(): void
    {
        $pemilikA = User::factory()->create();
        $rentalA = Rental::factory()->for($pemilikA)->create();
        $unitA = Unit::factory()->for($rentalA)->create();

        $pemilikB = User::factory()->create();
        Rental::factory()->for($pemilikB)->create();

        $this->actingAs($pemilikB)
            ->get(route('unit.edit', $unitA))
            ->assertForbidden();

        $this->actingAs($pemilikB)
            ->put(route('unit.update', $unitA), [
                'nama' => 'Bajakan',
                'tarif_per_hari' => 1000,
                'tarif_per_jam' => 1000,
                'minimal_sewa_jam' => 1,
                'toleransi_menit' => 15,
            ])
            ->assertForbidden();

        $this->assertNotSame('Bajakan', $unitA->fresh()->nama);
    }

    public function test_pemilik_tidak_bisa_menghapus_unit_rental_orang_lain(): void
    {
        $pemilikA = User::factory()->create();
        $rentalA = Rental::factory()->for($pemilikA)->create();
        $unitA = Unit::factory()->for($rentalA)->create();
        AturanTarif::factory()->for($unitA)->create();

        $pemilikB = User::factory()->create();
        Rental::factory()->for($pemilikB)->create();

        $this->actingAs($pemilikB)
            ->delete(route('unit.destroy', $unitA))
            ->assertForbidden();

        $this->assertDatabaseHas('unit', ['id' => $unitA->id]);
    }

    public function test_daftar_unit_hanya_menampilkan_unit_sendiri(): void
    {
        $pemilikA = User::factory()->create();
        $rentalA = Rental::factory()->for($pemilikA)->create();
        Unit::factory()->for($rentalA)->create(['nama' => 'Unit Milik A']);

        $pemilikB = User::factory()->create();
        $rentalB = Rental::factory()->for($pemilikB)->create();
        Unit::factory()->for($rentalB)->create(['nama' => 'Unit Milik B']);

        $this->actingAs($pemilikB)
            ->get(route('unit.index'))
            ->assertOk()
            ->assertSee('Unit Milik B')
            ->assertDontSee('Unit Milik A');
    }
}
