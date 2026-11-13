<?php

namespace App\Models;

use Database\Factories\AturanTarifFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AturanTarif extends Model
{
    /** @use HasFactory<AturanTarifFactory> */
    use HasFactory;

    protected $table = 'aturan_tarif';

    protected $fillable = [
        'unit_id',
        'tarif_per_hari',
        'tarif_per_jam',
        'minimal_sewa_jam',
        'toleransi_menit',
    ];

    protected function casts(): array
    {
        return [
            'tarif_per_hari' => 'decimal:2',
            'tarif_per_jam' => 'decimal:2',
            'minimal_sewa_jam' => 'integer',
            'toleransi_menit' => 'integer',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
