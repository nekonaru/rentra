<?php

namespace Database\Factories;

use App\Models\PriceRule;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceRule>
 */
class PriceRuleFactory extends Factory
{
    protected $model = PriceRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'tarif_per_hari' => fake()->randomElement([150000, 250000, 350000, 500000]),
            'tarif_per_jam' => fake()->randomElement([25000, 40000, 60000, 75000]),
            'minimal_sewa_jam' => 3,
            'toleransi_menit' => 15,
        ];
    }
}
