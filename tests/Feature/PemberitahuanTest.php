<?php

namespace Tests\Feature;

use App\Enums\StatusPemesanan;
use App\Mail\BuktiMasuk;
use App\Models\AturanTarif;
use App\Models\Pemesanan;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PemberitahuanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilik_mendapat_email_saat_bukti_masuk(): void
    {
        Storage::fake('local');
        Mail::fake();

        $pemilik = User::factory()->create(['email' => 'pemilik@rentra.test']);
        $rental = Rental::factory()->for($pemilik)->create(['slug' => 'kamera-kita']);
        $unit = Unit::factory()->for($rental)->create();
        AturanTarif::factory()->for($unit)->create();

        $pemesanan = Pemesanan::factory()->for($unit)->create([
            'rental_id' => $rental->id,
            'status' => StatusPemesanan::Ditahan,
            'tahan_sampai' => now()->addMinutes(30),
        ]);

        $this->post("/pemesanan/{$pemesanan->kode}/bukti", [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect(route('publik.pemesanan', $pemesanan->kode));

        Mail::assertSent(BuktiMasuk::class, fn ($mail) => $mail->hasTo('pemilik@rentra.test'));
    }
}
