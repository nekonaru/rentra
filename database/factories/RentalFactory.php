<?php

namespace Database\Factories;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Rental>
 */
class RentalFactory extends Factory
{
    protected $model = Rental::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake('id_ID')->company().' Rental';

        return [
            'user_id' => User::factory(),
            'nama' => $nama,
            'slug' => Str::slug($nama.'-'.fake()->unique()->numberBetween(1, 999999)),
            'no_wa' => '628'.fake()->numerify('##########'),
            'info_rekening' => 'BCA 1234567890 a.n. '.fake('id_ID')->name(),
            'durasi_hold_menit' => 60,
            'persen_dp' => 50,
        ];
    }
}
