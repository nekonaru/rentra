<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\UnitBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitBlock>
 */
class UnitBlockFactory extends Factory
{
    protected $model = UnitBlock::class;

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
