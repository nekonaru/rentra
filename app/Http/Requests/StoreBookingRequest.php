<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'nama_penyewa' => ['required', 'string', 'max:120'],
            'wa' => ['required', 'regex:/^62[0-9]{8,14}$/'],
            'mulai' => ['required', 'date', 'after:now'],
            'selesai' => ['required', 'date', 'after:mulai'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'wa.regex' => 'Nomor WA harus format 62 diikuti angka, tanpa spasi atau tanda +.',
            'mulai.after' => 'Waktu mulai harus di masa depan.',
            'selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ];
    }
}
