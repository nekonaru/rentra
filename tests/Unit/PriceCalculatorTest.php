<?php

namespace Tests\Unit;

use App\Services\PriceCalculator;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PriceCalculator;
    }

    public function test_exactly_one_day_costs_one_day_rate(): void
    {
        $result = $this->calculator->hitung(
            350_000, 50_000, 3, 0,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-14 09:00'),
        );

        $this->assertSame(1, $result['hari']);
        $this->assertSame(0.0, $result['sisa_jam']);
        $this->assertSame(350_000.0, $result['total']);
    }

    public function test_tolerance_absorbs_short_overruns(): void
    {
        $result = $this->calculator->hitung(
            350_000, 50_000, 3, 30,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-15 09:30'),
        );

        $this->assertSame(2, $result['hari']);
        $this->assertSame(700_000.0, $result['total']);
    }

    public function test_minimal_sewa_applies_to_short_bookings(): void
    {
        $result = $this->calculator->hitung(
            350_000, 50_000, 3, 15,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-13 09:45'),
        );

        // 45 minutes booked with 15 minute tolerance = 30 billed minutes,
        // but minimal sewa is 3 hours.
        $this->assertSame(150_000.0, $result['total']);
    }

    public function test_two_days_and_six_hours(): void
    {
        $result = $this->calculator->hitung(
            350_000, 50_000, 3, 15,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-15 15:00'),
        );

        // 2 days + (6h - 15min tolerance = 5.75h) = 700.000 + min(5.75 * 50.000, 350.000)
        $this->assertSame(2, $result['hari']);
        $this->assertSame(287_500.0, $result['jam_part']);
        $this->assertSame(987_500.0, $result['total']);
    }

    public function test_remaining_hours_are_capped_at_one_day_rate(): void
    {
        $result = $this->calculator->hitung(
            350_000, 50_000, 3, 0,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-14 19:00'), // 1 day 10 hours
        );

        $this->assertSame(1, $result['hari']);
        // 10 hours x 50.000 = 500.000, capped at one day rate.
        $this->assertSame(350_000.0, $result['jam_part']);
        $this->assertSame(700_000.0, $result['total']);
    }

    public function test_cross_month_booking(): void
    {
        $result = $this->calculator->hitung(
            500_000, 75_000, 3, 15,
            new DateTimeImmutable('2026-11-29 10:00'),
            new DateTimeImmutable('2026-12-01 10:00'),
        );

        // 2 days booked with 15 minute tolerance bills 47:45, i.e. 1 full day
        // plus 23.75 hours. Remaining hours are capped at the day rate.
        $this->assertSame(1, $result['hari']);
        $this->assertSame(23.75, $result['sisa_jam']);
        $this->assertSame(500_000.0, $result['jam_part']);
        $this->assertSame(1_000_000.0, $result['total']);
    }

    public function test_dp_is_percentage_of_total(): void
    {
        $result = $this->calculator->hitung(
            350_000, 50_000, 3, 0,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-15 09:00'),
            50,
        );

        $this->assertSame(700_000.0, $result['total']);
        $this->assertSame(350_000.0, $result['dp_nominal']);
    }

    public function test_selesai_before_mulai_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->hitung(
            350_000, 50_000, 3, 15,
            new DateTimeImmutable('2026-11-14 09:00'),
            new DateTimeImmutable('2026-11-13 09:00'),
        );
    }
}
