<?php

namespace Database\Factories;

use App\Models\BlokUnit;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlokUnit>
 */
class BlokUnitFactory extends Factory
{
    protected $model = BlokUnit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mulai = now()->addDays(fake()->numberBetween(1, 30))->setTime(9, 0);

        return [
            'unit_id' => Unit::factory(),
            'mulai' => $mulai,
            'selesai' => (clone $mulai)->addDay(),
            'alasan' => 'Servis',
        ];
    }
}
