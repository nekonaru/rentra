<?php

namespace Database\Factories;

use App\Models\Rental;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake('id_ID')->randomElement([
            'Sony A7 III', 'Canon EOS R6', 'Nikon Z6 II',
            'Lensa 35mm f/1.8', 'Lensa 50mm f/1.4', 'DJI Mavic Air 2',
            'Godox TT685', 'Tripod Manfrotto',
        ]);

        return [
            'rental_id' => Rental::factory(),
            'nama' => $nama,
            'kategori' => fake('id_ID')->randomElement(['Kamera', 'Lensa', 'Drone', 'Aksesoris']),
            'foto_path' => null,
            'aktif' => true,
        ];
    }
}
