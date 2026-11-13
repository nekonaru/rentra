<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class BookingsSweep extends Command
{
    /**
     * @var string
     */
    protected $signature = 'bookings:sweep';

    /**
     * @var string
     */
    protected $description = 'Kedaluwarsa booking yang hold-nya habis dan tandai booking yang terlambat';

    public function handle(BookingService $bookingService): int
    {
        $updated = $bookingService->sweepExpired();

        $this->info("bookings:sweep selesai. {$updated} booking diperbarui.");

        return self::SUCCESS;
    }
}
