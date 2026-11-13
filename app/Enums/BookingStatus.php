<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case Ditahan = 'ditahan';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Terkonfirmasi = 'terkonfirmasi';
    case SedangDisewa = 'sedang_disewa';
    case Selesai = 'selesai';
    case Kedaluwarsa = 'kedaluwarsa';
    case Terlambat = 'terlambat';
    case Dibatalkan = 'dibatalkan';

    /**
     * Statuses that reserve a unit slot. Used for anti-clash checks and
     * availability calculation.
     *
     * @return array<int, BookingStatus>
     */
    public static function slotReserve(): array
    {
        return [
            self::Ditahan,
            self::MenungguVerifikasi,
            self::Terkonfirmasi,
            self::SedangDisewa,
            self::Terlambat,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Ditahan => 'Ditahan',
            self::MenungguVerifikasi => 'Menunggu verifikasi',
            self::Terkonfirmasi => 'Terkonfirmasi',
            self::SedangDisewa => 'Sedang disewa',
            self::Selesai => 'Selesai',
            self::Kedaluwarsa => 'Kedaluwarsa',
            self::Terlambat => 'Terlambat',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /**
     * Hex color for badges and calendar dots. Always pair with label().
     */
    public function color(): string
    {
        return match ($this) {
            self::Ditahan => '#F59E0B',
            self::MenungguVerifikasi => '#2563EB',
            self::Terkonfirmasi, self::SedangDisewa => '#0F766E',
            self::Terlambat => '#DC2626',
            self::Selesai => '#16A34A',
            self::Kedaluwarsa, self::Dibatalkan => '#6B7280',
            self::Draft => '#6B7280',
        };
    }
}
