<?php

namespace Tests\Feature;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_rental_and_is_redirected_to_unit_step(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'nama' => 'Kamera Kita',
            'slug' => 'kamera-kita',
            'no_wa' => '6281234567890',
            'info_rekening' => 'BCA 1234567890 a.n. Kita',
            'durasi_hold_menit' => 60,
            'persen_dp' => 50,
        ]);

        $response->assertRedirect(route('onboarding.unit'));
        $this->assertDatabaseHas('rentals', ['slug' => 'kamera-kita', 'user_id' => $user->id]);
    }

    public function test_reserved_slug_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'nama' => 'Dashboard Rental',
            'slug' => 'dashboard',
            'no_wa' => '6281234567890',
            'durasi_hold_menit' => 60,
            'persen_dp' => 50,
        ]);

        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseMissing('rentals', ['slug' => 'dashboard']);
    }

    public function test_invalid_wa_format_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'nama' => 'Kamera Kita',
            'slug' => 'kamera-kita',
            'no_wa' => '08123456789',
            'durasi_hold_menit' => 60,
            'persen_dp' => 50,
        ]);

        $response->assertSessionHasErrors('no_wa');
    }

    public function test_user_with_rental_is_redirected_away_from_onboarding(): void
    {
        $user = User::factory()->create();
        Rental::factory()->for($user)->create();

        $this->actingAs($user)->get(route('onboarding.create'))->assertRedirect(route('dashboard'));
    }

    public function test_user_without_rental_is_redirected_to_onboarding_from_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('onboarding.create'));
    }
}
