<?php

namespace App\Models;

use Database\Factories\BlokUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlokUnit extends Model
{
    /** @use HasFactory<BlokUnitFactory> */
    use HasFactory;

    protected $table = 'blok_unit';

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
