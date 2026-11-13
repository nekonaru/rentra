<?php

namespace App\Models;

use Database\Factories\UnitBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitBlock extends Model
{
    /** @use HasFactory<UnitBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'mulai',
        'selesai',
        'alasan',
    ];

    protected function casts(): array
    {
        return [
            'mulai' => 'datetime',
            'selesai' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
