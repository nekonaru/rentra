<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = ['judul', 'isi', 'ditampilkan_sampai'];

    protected function casts(): array
    {
        return ['ditampilkan_sampai' => 'datetime'];
    }

    public function scopeAktif($query)
    {
        return $query->where(fn ($q) => $q->whereNull('ditampilkan_sampai')->orWhere('ditampilkan_sampai', '>=', now()))
            ->latest();
    }
}
