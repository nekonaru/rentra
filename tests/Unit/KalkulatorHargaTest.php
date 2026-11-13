<?php

namespace Tests\Unit;

use App\Services\KalkulatorHarga;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class KalkulatorHargaTest extends TestCase
{
    private KalkulatorHarga $kalkulator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kalkulator = new KalkulatorHarga;
    }

    public function test_tepat_batas_satu_hari_seharga_tarif_harian(): void
    {
        $hasil = $this->kalkulator->hitung(
            350_000, 50_000, 3, 0,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-14 09:00'),
        );

        $this->assertSame(1, $hasil['hari']);
        $this->assertSame(0.0, $hasil['sisa_jam']);
        $this->assertSame(350_000.0, $hasil['total']);
    }

    public function test_toleransi_menyerap_kelebihan_waktu_singkat(): void
    {
        $hasil = $this->kalkulator->hitung(
            350_000, 50_000, 3, 30,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-15 09:30'),
        );

        $this->assertSame(2, $hasil['hari']);
        $this->assertSame(700_000.0, $hasil['total']);
    }

    public function test_minimal_sewa_berlaku_untuk_sewa_pendek(): void
    {
        $hasil = $this->kalkulator->hitung(
            350_000, 50_000, 3, 15,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-13 09:45'),
        );

        // 45 menit disewa dengan toleransi 15 menit = 30 menit ditagih,
        // tapi minimal sewa 3 jam.
        $this->assertSame(150_000.0, $hasil['total']);
    }

    public function test_sewa_dua_hari_enam_jam(): void
    {
        $hasil = $this->kalkulator->hitung(
            350_000, 50_000, 3, 15,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-15 15:00'),
        );

        // 2 hari + (6 jam - 15 menit toleransi = 5,75 jam) = 700.000 + min(5,75 x 50.000, 350.000)
        $this->assertSame(2, $hasil['hari']);
        $this->assertSame(287_500.0, $hasil['jam_part']);
        $this->assertSame(987_500.0, $hasil['total']);
    }

    public function test_sisa_jam_mentok_di_tarif_harian(): void
    {
        $hasil = $this->kalkulator->hitung(
            350_000, 50_000, 3, 0,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-14 19:00'), // 1 hari 10 jam
        );

        $this->assertSame(1, $hasil['hari']);
        // 10 jam x 50.000 = 500.000, mentok di tarif harian.
        $this->assertSame(350_000.0, $hasil['jam_part']);
        $this->assertSame(700_000.0, $hasil['total']);
    }

    public function test_lintas_bulan(): void
    {
        $hasil = $this->kalkulator->hitung(
            500_000, 75_000, 3, 15,
            new DateTimeImmutable('2026-11-29 10:00'),
            new DateTimeImmutable('2026-12-01 10:00'),
        );

        // 2 hari disewa dengan toleransi 15 menit ditagih 47:45,
        // yaitu 1 hari penuh + 23,75 jam. Jam sisa mentok di tarif harian.
        $this->assertSame(1, $hasil['hari']);
        $this->assertSame(23.75, $hasil['sisa_jam']);
        $this->assertSame(500_000.0, $hasil['jam_part']);
        $this->assertSame(1_000_000.0, $hasil['total']);
    }

    public function test_dp_persen_dari_total(): void
    {
        $hasil = $this->kalkulator->hitung(
            350_000, 50_000, 3, 0,
            new DateTimeImmutable('2026-11-13 09:00'),
            new DateTimeImmutable('2026-11-15 09:00'),
            50,
        );

        $this->assertSame(700_000.0, $hasil['total']);
        $this->assertSame(350_000.0, $hasil['dp_nominal']);
    }

    public function test_selesai_sebelum_mulai_melempar_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->kalkulator->hitung(
            350_000, 50_000, 3, 15,
            new DateTimeImmutable('2026-11-14 09:00'),
            new DateTimeImmutable('2026-11-13 09:00'),
        );
    }
}
