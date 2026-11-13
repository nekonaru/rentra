<?php

namespace App\Enums;

enum StatusPemesanan: string
{
    case Draf = 'draf';
    case Ditahan = 'ditahan';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Terkonfirmasi = 'terkonfirmasi';
    case SedangDisewa = 'sedang_disewa';
    case Selesai = 'selesai';
    case Kedaluwarsa = 'kedaluwarsa';
    case Terlambat = 'terlambat';
    case Dibatalkan = 'dibatalkan';

    /**
     * Status yang menahan slot unit. Dipakai untuk cek anti bentrok dan
     * perhitungan ketersediaan.
     *
     * @return array<int, StatusPemesanan>
     */
    public static function menahanSlot(): array
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
            self::Draf => 'Draf',
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
     * Warna hex untuk badge dan kalender. Selalu dipasangkan dengan label().
     */
    public function warna(): string
    {
        return match ($this) {
            self::Ditahan => '#F59E0B',
            self::MenungguVerifikasi => '#2563EB',
            self::Terkonfirmasi, self::SedangDisewa => '#0F766E',
            self::Terlambat => '#DC2626',
            self::Selesai => '#16A34A',
            self::Kedaluwarsa, self::Dibatalkan => '#6B7280',
            self::Draf => '#6B7280',
        };
    }
}
