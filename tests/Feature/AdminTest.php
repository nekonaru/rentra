<?php

namespace Tests\Feature;

use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function buatTenant(?User $pemilik = null): Rental
    {
        $pemilik ??= User::factory()->create();
        $rental = Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        return $rental;
    }

    public function test_admin_bisa_membuka_panel(): void
    {
        $admin = User::factory()->admin()->create();
        $this->buatTenant();

        $this->actingAs($admin)->get(route('admin.index'))->assertOk()->assertSee('Dashboard Admin');
        $this->actingAs($admin)->get(route('admin.tenant.index'))->assertOk();
    }

    public function test_pemilik_ditolak_di_panel_admin(): void
    {
        $pemilik = User::factory()->create();
        Rental::factory()->for($pemilik)->create(['slug' => 'milik-saya']);

        $this->actingAs($pemilik)->get(route('admin.index'))->assertForbidden();
        $this->actingAs($pemilik)->get(route('admin.tenant.index'))->assertForbidden();
    }

    public function test_tamu_dialirkan_ke_login(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('login'));
    }

    public function test_admin_bisa_menangguhkan_dan_mengaktifkan_tenant(): void
    {
        $admin = User::factory()->admin()->create();
        $rental = $this->buatTenant();

        $this->actingAs($admin)->post(route('admin.tenant.tangguhkan', $rental))->assertSessionHas('status');
        $this->assertFalse($rental->fresh()->aktif);

        $this->get("/{$rental->slug}")->assertNotFound();

        $this->actingAs($admin)->post(route('admin.tenant.aktifkan', $rental));
        $this->assertTrue($rental->fresh()->aktif);

        $this->get("/{$rental->slug}")->assertOk();
    }

    public function test_admin_tidak_melihat_identitas_penyewa(): void
    {
        $admin = User::factory()->admin()->create();
        $rental = $this->buatTenant();

        Pemesanan::factory()->for($rental->unit()->first())->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'nama_penyewa' => 'Penyewa Rahasia Sekali',
        ]);

        $this->actingAs($admin)->get(route('admin.tenant.show', $rental))->assertOk()->assertDontSee('Penyewa Rahasia Sekali');
    }

    public function test_admin_dialihkan_ke_panel_admin_dari_area_pemilik(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.index'));
    }
}
