<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnggahBuktiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bukti.required' => 'Pilih file bukti DP dulu.',
            'bukti.mimes' => 'Bukti harus jpg, png, atau pdf.',
            'bukti.mimetypes' => 'Isi file tidak cocok dengan gambar atau pdf.',
            'bukti.max' => 'Ukuran bukti maksimal 5 MB.',
        ];
    }
}
