<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TolakBuktiRequest extends FormRequest
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
            'catatan_tolak' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'catatan_tolak.required' => 'Isi alasan penolakan supaya penyewa paham.',
        ];
    }
}
