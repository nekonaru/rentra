<?php

namespace Tests\Feature;

use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_membuat_tenant(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.tenant.simpan'), [
            'nama' => 'Kamera Kita Baru',
            'slug' => 'kamera-kita-baru',
            'no_wa' => '6281234567890',
            'nama_pemilik' => 'Pemilik Baru',
            'email_pemilik' => 'pemilik-baru@rentra.test',
        ]);

        $response->assertRedirect(route('admin.tenant.index'));
        $this->assertDatabaseHas('rental', ['slug' => 'kamera-kita-baru']);
        $this->assertDatabaseHas('users', ['email' => 'pemilik-baru@rentra.test', 'role' => 'pemilik']);
    }

    public function test_slug_terlarang_ditolak_saat_membuat_tenant(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.tenant.simpan'), [
            'nama' => 'Dashboard Rental',
            'slug' => 'dashboard',
            'no_wa' => '6281234567890',
            'nama_pemilik' => 'Pemilik',
            'email_pemilik' => 'x@rentra.test',
        ])->assertSessionHasErrors('slug');

        $this->assertDatabaseMissing('rental', ['slug' => 'dashboard']);
    }

    public function test_pemilik_tidak_bisa_membuat_tenant(): void
    {
        $pemilik = User::factory()->create();

        $this->actingAs($pemilik)->post(route('admin.tenant.simpan'), [
            'nama' => 'Cobaan',
            'slug' => 'cobaan',
            'no_wa' => '6281234567890',
            'nama_pemilik' => 'X',
            'email_pemilik' => 'x@rentra.test',
        ])->assertForbidden();

        $this->assertDatabaseMissing('rental', ['slug' => 'cobaan']);
    }

    public function test_admin_bisa_mencari_tenant(): void
    {
        $admin = User::factory()->admin()->create();

        $pemilikA = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@rentra.test']);
        $rentalA = Rental::factory()->for($pemilikA)->create(['nama' => 'Kamera Budi', 'slug' => 'kamera-budi']);
        Unit::factory()->for($rentalA)->create();

        $pemilikB = User::factory()->create(['name' => 'Siti Aminah']);
        $rentalB = Rental::factory()->for($pemilikB)->create(['nama' => 'Lensa Siti', 'slug' => 'lensa-siti']);
        Unit::factory()->for($rentalB)->create();

        $this->actingAs($admin)->get(route('admin.tenant.index', ['q' => 'Kamera']))
            ->assertOk()
            ->assertSee('Kamera Budi')
            ->assertDontSee('Lensa Siti');
    }

    public function test_cari_tidak_pecah_paginasi(): void
    {
        $admin = User::factory()->admin()->create();

        Rental::factory()->for(User::factory())->create(['nama' => 'Renthal A', 'slug' => 'rental-a']);
        Rental::factory()->for(User::factory())->create(['nama' => 'Rental B', 'slug' => 'rental-b']);

        $this->actingAs($admin)->get(route('admin.tenant.index', ['q' => 'Rental']))->assertOk();
        $this->actingAs($admin)->get(route('admin.tenant.index', ['q' => '']))->assertOk();
    }
}
