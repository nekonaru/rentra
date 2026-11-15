<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrasiPintuMasukTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_baru_langsung_bisa_membuka_dashboard_tanpa_verifikasi_email(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Pemilik Baru',
            'email' => 'pemilik-baru@rentra.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Setelah registrasi, user diarahkan ke onboarding (alur yang benar),
        // bukan ke halaman verifikasi email yang tidak akan pernah sampai.
        $this->actingAs(\App\Models\User::where('email', 'pemilik-baru@rentra.test')->firstOrFail())
            ->get(route('dashboard'))
            ->assertRedirect(route('onboarding.buat'));
    }

    public function test_pemilik_dengan_rental_bisa_akses_dashboard_tanpa_verifikasi_email(): void
    {
        $pemilik = \App\Models\User::factory()->create([
            'email_verified_at' => null, // belum terverifikasi
        ]);
        \App\Models\Rental::factory()->for($pemilik)->create(['slug' => 'milik-saya']);

        $this->actingAs($pemilik)->get(route('dashboard'))->assertOk();
    }
}
