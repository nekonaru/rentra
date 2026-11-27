<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    protected $table = 'unit';

    protected $fillable = [
        'rental_id',
        'nama',
        'kategori',
        'spesifikasi',
        'merek',
        'tahun',
        'jalur_foto',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function aturanTarif(): HasOne
    {
        return $this->hasOne(AturanTarif::class);
    }

    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    public function blok(): HasMany
    {
        return $this->hasMany(BlokUnit::class);
    }

    public function foto(): HasMany
    {
        return $this->hasMany(FotoUnit::class)->orderBy('urutan');
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class)->where('status', 'tampil');
    }
}
