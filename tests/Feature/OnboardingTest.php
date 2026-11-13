<?php

namespace Tests\Feature;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_bisa_membuat_rental_dan_diarahkan_ke_langkah_unit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.simpan'), [
            'nama' => 'Kamera Kita',
            'slug' => 'kamera-kita',
            'no_wa' => '6281234567890',
            'info_rekening' => 'BCA 1234567890 a.n. Kita',
            'durasi_tahan_menit' => 60,
            'persen_dp' => 50,
        ]);

        $response->assertRedirect(route('onboarding.unit'));
        $this->assertDatabaseHas('rental', ['slug' => 'kamera-kita', 'user_id' => $user->id]);
    }

    public function test_slug_terlarang_ditolak(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.simpan'), [
            'nama' => 'Dashboard Rental',
            'slug' => 'dashboard',
            'no_wa' => '6281234567890',
            'durasi_tahan_menit' => 60,
            'persen_dp' => 50,
        ]);

        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseMissing('rental', ['slug' => 'dashboard']);
    }

    public function test_format_wa_invalid_ditolak(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.simpan'), [
            'nama' => 'Kamera Kita',
            'slug' => 'kamera-kita',
            'no_wa' => '08123456789',
            'durasi_tahan_menit' => 60,
            'persen_dp' => 50,
        ]);

        $response->assertSessionHasErrors('no_wa');
    }

    public function test_user_yang_sudah_punya_rental_diarahkan_dari_onboarding(): void
    {
        $user = User::factory()->create();
        Rental::factory()->for($user)->create();

        $this->actingAs($user)->get(route('onboarding.buat'))->assertRedirect(route('dashboard'));
    }

    public function test_user_tanpa_rental_diarahkan_ke_onboarding_dari_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('onboarding.buat'));
    }
}
