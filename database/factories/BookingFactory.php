<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mulai = now()->addDays(fake()->numberBetween(1, 30))->setTime(9, 0);
        $selesai = (clone $mulai)->addDays(2)->setTime(9, 0);

        return [
            'kode' => strtoupper(Str::random(10)),
            'unit_id' => Unit::factory(),
            'rental_id' => fn (array $attributes) => Unit::find($attributes['unit_id'])?->rental_id,
            'nama_penyewa' => fake('id_ID')->name(),
            'wa' => '628'.fake()->numerify('##########'),
            'mulai' => $mulai,
            'selesai' => $selesai,
            'total' => 500000,
            'dp_nominal' => 250000,
            'status' => BookingStatus::Ditahan,
            'hold_expires_at' => now()->addMinutes(60),
        ];
    }

    public function terkonfirmasi(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Terkonfirmasi,
            'hold_expires_at' => null,
        ]);
    }
}
