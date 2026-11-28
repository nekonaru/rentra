<?php

namespace Tests\Feature;

use App\Models\AturanTarif;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiturLanjutanTest extends TestCase
{
    use RefreshDatabase;

    private function buatRental(?User $pemilik = null): Rental
    {
        $pemilik ??= User::factory()->create();
        $rental = Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']);
        $unit1 = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit1)->create();
        $unit2 = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit2)->create();

        return $rental;
    }

    public function test_riwayat_aktivitas_tampil(): void
    {
        $pemilik = User::factory()->create();
        $rental = $this->buatRental($pemilik);
        $rental->aktivitas()->create([
            'user_id' => $pemilik->id,
            'aktivitas' => 'Menambah unit',
            'keterangan' => 'Sony A7 III',
        ]);

        $this->actingAs($pemilik)->get(route('aktivitas.index'))
            ->assertOk()
            ->assertSee('Menambah unit');
    }

    public function test_aksi_massal_unit(): void
    {
        $pemilik = User::factory()->create();
        $rental = $this->buatRental($pemilik);
        $ids = $rental->unit()->pluck('id')->all();

        $this->actingAs($pemilik)->post(route('unit.aksi-massal'), [
            'ids' => $ids,
            'aksi' => 'nonaktifkan',
        ])->assertSessionHas('status');

        $this->assertSame(0, $rental->unit()->where('aktif', true)->count());
    }

    public function test_booking_multi_unit_dengan_kode_grup(): void
    {
        $pemilik = User::factory()->create();
        $rental = $this->buatRental($pemilik);
        $ids = $rental->unit()->pluck('id')->all();

        $this->actingAs($pemilik)->post(route('pemesanan.simpan-tambah'), [
            'unit_ids' => $ids,
            'nama_penyewa' => 'Penyewa Grup',
            'wa' => '628111222333',
            'mulai' => now()->addDays(5)->setTime(9, 0)->format('Y-m-d H:i:s'),
            'selesai' => now()->addDays(6)->setTime(9, 0)->format('Y-m-d H:i:s'),
        ])->assertSessionHas('status');

        $this->assertSame(2, $rental->pemesanan()->count());
        $this->assertNotNull($rental->pemesanan()->first()->kode_group);
    }

    public function test_admin_bisa_buat_pengumuman(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.pengumuman.store'), [
            'judul' => 'Fitur baru rilis',
            'isi' => 'Sekarang bisa booking beberapa unit sekaligus.',
        ])->assertSessionHas('status');

        $pemilik = User::factory()->create();
        $this->buatRental($pemilik);
        $this->actingAs($pemilik)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Fitur baru rilis');
    }
}
