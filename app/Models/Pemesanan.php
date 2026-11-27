<?php

namespace App\Models;

use App\Enums\StatusPemesanan;
use Database\Factories\PemesananFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pemesanan extends Model
{
    /** @use HasFactory<PemesananFactory> */
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'kode',
        'kode_promo',
        'unit_id',
        'rental_id',
        'nama_penyewa',
        'wa',
        'mulai',
        'selesai',
        'total',
        'diskon_nominal',
        'dp_nominal',
        'denda_nominal',
        'status',
        'tahan_sampai',
        'jalur_bukti',
        'catatan_tolak',
    ];

    protected function casts(): array
    {
        return [
            'mulai' => 'datetime',
            'selesai' => 'datetime',
            'tahan_sampai' => 'datetime',
            'total' => 'decimal:2',
            'diskon_nominal' => 'decimal:2',
            'dp_nominal' => 'decimal:2',
            'denda_nominal' => 'decimal:2',
            'status' => StatusPemesanan::class,
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Pemesanan yang menahan slot sekarang. Hold yang kedaluwarsa diabaikan
     * walau scheduler belum menyapunya.
     */
    public function scopeMenahanSlot(Builder $query): Builder
    {
        $statusMenahan = array_map(
            fn (StatusPemesanan $status) => $status->value,
            StatusPemesanan::menahanSlot(),
        );

        return $query->whereIn('status', $statusMenahan)
            ->where(function (Builder $q) {
                $q->where('status', '!=', StatusPemesanan::Ditahan->value)
                    ->orWhere('tahan_sampai', '>', now());
            });
    }

    public function scopeTumpangTindih(Builder $query, $mulai, $selesai): Builder
    {
        return $query->where('mulai', '<', $selesai)
            ->where('selesai', '>', $mulai);
    }
}
