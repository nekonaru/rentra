<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Re-encode gambar dari upload supaya EXIF/metadata (mis. lokasi GPS)
 * dan payload yang disisipkan hilang. Menolak file yang bukan gambar asli
 * atau yang dimensinya tidak wajar.
 */
class PembersihGambar
{
    /** Lebar/tinggi maksimum gambar (piksel). */
    private const BATAS_DIMENSI = 4000;

    /**
     * Bersihkan gambar dan kembalikan konten file baru (bytes).
     *
     * @throws InvalidArgumentException
     */
    public function bersihkan(string $pathAsli, string $mime): string
    {
        $info = @getimagesize($pathAsli);
        if ($info === false || ! isset($info[0], $info[1], $info['mime'])) {
            throw new InvalidArgumentException('File bukan gambar asli.');
        }
        if ($info[0] > self::BATAS_DIMENSI || $info[1] > self::BATAS_DIMENSI) {
            throw new InvalidArgumentException('Dimensi gambar terlalu besar.');
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($pathAsli),
            'image/png' => @imagecreatefrompng($pathAsli),
            'image/webp' => @imagecreatefromwebp($pathAsli),
            default => false,
        };

        if ($image === false) {
            throw new InvalidArgumentException('Gambar rusak atau tidak bisa dibaca.');
        }

        if ($mime === 'image/png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        ob_start();
        if ($mime === 'image/jpeg') {
            imagejpeg($image, null, 90);
        } elseif ($mime === 'image/webp') {
            imagewebp($image, null, 90);
        } else {
            imagepng($image, null, 9);
        }
        $bersih = (string) ob_get_clean();
        imagedestroy($image);

        return $bersih;
    }
}
