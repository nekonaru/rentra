<?php

namespace App\Console\Commands;

use App\Services\LayananPemesanan;
use Illuminate\Console\Command;

class SapuPemesanan extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pemesanan:sapu';

    /**
     * @var string
     */
    protected $description = 'Kedaluwarsakan pemesanan yang hold-nya habis dan tandai yang terlambat';

    public function handle(LayananPemesanan $layananPemesanan): int
    {
        $diperbarui = $layananPemesanan->sapuKedaluwarsa();

        $this->info("pemesanan:sapu selesai. {$diperbarui} pemesanan diperbarui.");

        return self::SUCCESS;
    }
}
