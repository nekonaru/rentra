<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\PriceRule;
use App\Models\Rental;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@rentra.id'],
            ['name' => 'Demo Pemilik', 'password' => Hash::make('password')],
        );

        $rental = Rental::updateOrCreate(
            ['slug' => 'demo-kamera'],
            [
                'user_id' => $user->id,
                'nama' => 'Demo Kamera Rental',
                'no_wa' => '6281234567890',
                'info_rekening' => 'BCA 1234567890 a.n. Demo Rentra',
                'durasi_hold_menit' => 60,
                'persen_dp' => 50,
            ],
        );

        $units = [
            ['nama' => 'Sony A7 III Kit 28-70mm', 'kategori' => 'Kamera', 'tarif_per_hari' => 350000, 'tarif_per_jam' => 50000],
            ['nama' => 'Lensa Sony 35mm f/1.8', 'kategori' => 'Lensa', 'tarif_per_hari' => 100000, 'tarif_per_jam' => 20000],
            ['nama' => 'DJI Mini 4 Pro', 'kategori' => 'Drone', 'tarif_per_hari' => 400000, 'tarif_per_jam' => 75000],
        ];

        foreach ($units as $data) {
            $unit = Unit::updateOrCreate(
                ['rental_id' => $rental->id, 'nama' => $data['nama']],
                ['kategori' => $data['kategori'], 'aktif' => true],
            );

            PriceRule::updateOrCreate(
                ['unit_id' => $unit->id],
                [
                    'tarif_per_hari' => $data['tarif_per_hari'],
                    'tarif_per_jam' => $data['tarif_per_jam'],
                    'minimal_sewa_jam' => 3,
                    'toleransi_menit' => 15,
                ],
            );
        }

        $kamera = $rental->units()->where('nama', 'Sony A7 III Kit 28-70mm')->firstOrFail();

        Booking::updateOrCreate(
            ['kode' => 'DEMO000001'],
            [
                'unit_id' => $kamera->id,
                'rental_id' => $rental->id,
                'nama_penyewa' => 'Budi Santoso',
                'wa' => '6281111111111',
                'mulai' => now()->addDays(2)->setTime(9, 0),
                'selesai' => now()->addDays(4)->setTime(9, 0),
                'total' => 700000,
                'dp_nominal' => 350000,
                'status' => BookingStatus::Terkonfirmasi,
                'hold_expires_at' => null,
            ],
        );

        $lensa = $rental->units()->where('nama', 'Lensa Sony 35mm f/1.8')->firstOrFail();

        Booking::updateOrCreate(
            ['kode' => 'DEMO000002'],
            [
                'unit_id' => $lensa->id,
                'rental_id' => $rental->id,
                'nama_penyewa' => 'Siti Aminah',
                'wa' => '6282222222222',
                'mulai' => now()->subDays(2)->setTime(9, 0),
                'selesai' => now()->subDay()->setTime(9, 0),
                'total' => 100000,
                'dp_nominal' => 50000,
                'status' => BookingStatus::Terlambat,
                'hold_expires_at' => null,
            ],
        );
    }
}
