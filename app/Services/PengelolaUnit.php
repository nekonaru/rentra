<?php

namespace App\Services;

use App\Models\AturanTarif;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PengelolaUnit
{
    /**
     * @param  array<string, mixed>  $data  Field unit yang sudah tervalidasi (nama, kategori, aktif) beserta kunci tarif_*.
     */
    public function buat(int $rentalId, array $data, ?UploadedFile $foto = null): Unit
    {
        $unit = Unit::create([
            'rental_id' => $rentalId,
            'nama' => $data['nama'],
            'kategori' => $data['kategori'] ?? null,
            'aktif' => $data['aktif'] ?? true,
            'jalur_foto' => $foto !== null ? $foto->store('unit', 'public') : null,
        ]);

        $this->simpanAturanTarif($unit, $data);

        return $unit;
    }

    /**
     * @param  array<string, mixed>  $data  Field unit yang sudah tervalidasi (nama, kategori, aktif, hapus_foto) beserta kunci tarif_*.
     */
    public function perbarui(Unit $unit, array $data, ?UploadedFile $foto = null): Unit
    {
        if (! empty($data['hapus_foto']) && $unit->jalur_foto !== null) {
            Storage::disk('public')->delete($unit->jalur_foto);
            $unit->jalur_foto = null;
        }

        if ($foto !== null) {
            if ($unit->jalur_foto !== null) {
                Storage::disk('public')->delete($unit->jalur_foto);
            }
            $unit->jalur_foto = $foto->store('unit', 'public');
        }

        $unit->fill([
            'nama' => $data['nama'],
            'kategori' => $data['kategori'] ?? null,
            'aktif' => $data['aktif'] ?? false,
        ])->save();

        $this->simpanAturanTarif($unit, $data);

        return $unit;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function simpanAturanTarif(Unit $unit, array $data): void
    {
        AturanTarif::updateOrCreate(
            ['unit_id' => $unit->id],
            [
                'tarif_per_hari' => $data['tarif_per_hari'],
                'tarif_per_jam' => $data['tarif_per_jam'],
                'minimal_sewa_jam' => $data['minimal_sewa_jam'],
                'toleransi_menit' => $data['toleransi_menit'],
            ],
        );
    }
}
