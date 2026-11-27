<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promo extends Model
{
    use HasFactory;

    protected $table = 'promo';

    protected $fillable = [
        'rental_id', 'kode', 'jenis', 'nilai', 'minimal_sewa_jam', 'kuota',
        'terpakai', 'berlaku_dari', 'berlaku_sampai', 'aktif',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'berlaku_dari' => 'datetime',
            'berlaku_sampai' => 'datetime',
            'aktif' => 'boolean',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function masihBerlaku(): bool
    {
        if (! $this->aktif || $this->terpakai >= $this->kuota) {
            return false;
        }
        if ($this->berlaku_dari !== null && now()->lt($this->berlaku_dari)) {
            return false;
        }
        if ($this->berlaku_sampai !== null && now()->gt($this->berlaku_sampai)) {
            return false;
        }

        return true;
    }

    public function diskon(float $total): float
    {
        return round($this->jenis === 'persen' ? $total * (float) $this->nilai / 100 : min((float) $this->nilai, $total), 2);
    }
}
