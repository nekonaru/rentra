<?php

namespace App\Services;

use App\Models\PriceRule;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UnitManager
{
    /**
     * @param  array<string, mixed>  $data  Validated unit fields (nama, kategori, aktif) plus tarif_* keys.
     */
    public function create(int $rentalId, array $data, ?UploadedFile $foto = null): Unit
    {
        $unit = Unit::create([
            'rental_id' => $rentalId,
            'nama' => $data['nama'],
            'kategori' => $data['kategori'] ?? null,
            'aktif' => $data['aktif'] ?? true,
            'foto_path' => $foto !== null ? $foto->store('units', 'public') : null,
        ]);

        $this->savePriceRule($unit, $data);

        return $unit;
    }

    /**
     * @param  array<string, mixed>  $data  Validated unit fields (nama, kategori, aktif, hapus_foto) plus tarif_* keys.
     */
    public function update(Unit $unit, array $data, ?UploadedFile $foto = null): Unit
    {
        if (! empty($data['hapus_foto']) && $unit->foto_path !== null) {
            Storage::disk('public')->delete($unit->foto_path);
            $unit->foto_path = null;
        }

        if ($foto !== null) {
            if ($unit->foto_path !== null) {
                Storage::disk('public')->delete($unit->foto_path);
            }
            $unit->foto_path = $foto->store('units', 'public');
        }

        $unit->fill([
            'nama' => $data['nama'],
            'kategori' => $data['kategori'] ?? null,
            'aktif' => $data['aktif'] ?? false,
        ])->save();

        $this->savePriceRule($unit, $data);

        return $unit;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function savePriceRule(Unit $unit, array $data): void
    {
        PriceRule::updateOrCreate(
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
