<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'kode',
        'unit_id',
        'rental_id',
        'nama_penyewa',
        'wa',
        'mulai',
        'selesai',
        'total',
        'dp_nominal',
        'status',
        'hold_expires_at',
        'bukti_path',
        'catatan_tolak',
    ];

    protected function casts(): array
    {
        return [
            'mulai' => 'datetime',
            'selesai' => 'datetime',
            'hold_expires_at' => 'datetime',
            'total' => 'decimal:2',
            'dp_nominal' => 'decimal:2',
            'status' => BookingStatus::class,
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
     * Bookings that reserve a slot right now. Expired holds are ignored even
     * if the scheduler has not swept them yet.
     */
    public function scopeReserving(Builder $query): Builder
    {
        $slotStatuses = array_map(
            fn (BookingStatus $status) => $status->value,
            BookingStatus::slotReserve(),
        );

        return $query->whereIn('status', $slotStatuses)
            ->where(function (Builder $q) {
                $q->where('status', '!=', BookingStatus::Ditahan->value)
                    ->orWhere('hold_expires_at', '>', now());
            });
    }

    public function scopeOverlapping(Builder $query, $mulai, $selesai): Builder
    {
        return $query->where('mulai', '<', $selesai)
            ->where('selesai', '>', $mulai);
    }
}
