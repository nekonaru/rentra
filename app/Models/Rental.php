<?php

namespace App\Models;

use Database\Factories\RentalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    /** @use HasFactory<RentalFactory> */
    use HasFactory;

    protected $table = 'rental';

    protected $fillable = [
        'user_id',
        'nama',
        'slug',
        'no_wa',
        'info_rekening',
        'durasi_tahan_menit',
        'persen_dp',
        'aktif',
        'logo_path',
        'warna_aksen',
        'deskripsi',
        'alamat',
        'peta_url',
    ];

    protected function casts(): array
    {
        return [
            'durasi_tahan_menit' => 'integer',
            'persen_dp' => 'integer',
            'aktif' => 'boolean',
            'jam_buka' => 'datetime:H:i',
            'jam_tutup' => 'datetime:H:i',
            'denda_per_jam' => 'decimal:2',
            'denda_maks' => 'decimal:2',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class);
    }

    public function promo(): HasMany
    {
        return $this->hasMany(Promo::class);
    }

    public function aktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class);
    }
}
