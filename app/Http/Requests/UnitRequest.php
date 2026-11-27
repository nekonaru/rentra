<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->rental()->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:120'],
            'kategori' => ['nullable', 'string', 'max:60'],
            'spesifikasi' => ['nullable', 'string', 'max:1000'],
            'merek' => ['nullable', 'string', 'max:80'],
            'tahun' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'foto_tambahan' => ['nullable', 'array', 'max:5'],
            'foto_tambahan.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto' => ['sometimes', 'boolean'],
            'aktif' => ['sometimes', 'boolean'],
            'tarif_per_hari' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'tarif_per_jam' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'minimal_sewa_jam' => ['required', 'integer', 'min:1', 'max:168'],
            'toleransi_menit' => ['required', 'integer', 'min:0', 'max:1440'],
        ];
    }
}
