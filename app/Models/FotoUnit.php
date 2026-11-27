<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoUnit extends Model
{
    use HasFactory;

    protected $table = 'foto_unit';

    protected $fillable = ['unit_id', 'jalur', 'urutan'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
