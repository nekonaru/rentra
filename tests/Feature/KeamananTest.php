<?php

namespace Tests\Feature;

use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KeamananTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_pemilik_mengalihkan_ke_login_saat_belum_masuk(): void
    {
        foreach (['/dashboard', '/unit', '/pemesanan', '/verifikasi', '/pengaturan', '/blok', '/onboarding'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_bukti_privat_tidak_bisa_diakses_tanpa_signature(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('bukti/test.jpg', 'isi-palsu');

        $response = $this->get('/storage/bukti/test.jpg');

        $this->assertContains($response->getStatusCode(), [403, 404]);
    }

    public function test_halaman_publik_mengirim_header_keamanan(): void
    {
        $response = $this->get('/');

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertStringContainsString("frame-ancestors 'none'", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("object-src 'none'", (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_post_dari_asal_asing_ditolak(): void
    {
        $rental = Rental::factory()->for(User::factory())->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        $this->withHeaders(['Origin' => 'https://jahat.contoh'])
            ->post("/{$rental->slug}/{$unit->id}/pratinjau-harga", [
                'mulai' => now()->addDay()->format('Y-m-d H:i:s'),
                'selesai' => now()->addDay()->addHours(4)->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();
    }

    public function test_ekspor_csv_terlindung_dari_formula_injection(): void
    {
        $pemilik = User::factory()->create();
        $rental = Rental::factory()->for($pemilik)->create(['slug' => 'milik-saya']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        $this->actingAs($pemilik);
        Pemesanan::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'nama_penyewa' => '=1+2',
        ]);

        $csv = $this->get(route('pemesanan.ekspor'))->streamedContent();
        $this->assertStringContainsString("'=1+2", $csv);
    }

    public function test_nama_penyewa_berisi_script_dirender_sebagai_teks(): void
    {
        $pemilik = User::factory()->create();
        $rental = Rental::factory()->for($pemilik)->create(['slug' => 'milik-saya']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        Pemesanan::factory()->for($unit)->terkonfirmasi()->create([
            'rental_id' => $rental->id,
            'nama_penyewa' => '<img src=x onerror=alert(1)>',
        ]);

        $response = $this->actingAs($pemilik)->get(route('pemesanan.index'));
        $response->assertOk();
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $response->getContent());
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $response->getContent());
    }

    public function test_password_disimpan_dengan_hash_bcrypt(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $this->assertStringStartsWith('$2y$', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_batas_percobaan_login(): void
    {
        User::factory()->create(['email' => 'pemilik@rentra.test', 'password' => 'rahasia123']);

        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('login'), [
                'email' => 'pemilik@rentra.test',
                'password' => 'salah',
            ]);
        }

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    public function test_file_sensitif_mengembalikan_404(): void
    {
        foreach (['/.env', '/.git/config', '/.git/HEAD', '/backup.zip', '/db.sql', '/phpinfo.php'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }
}
