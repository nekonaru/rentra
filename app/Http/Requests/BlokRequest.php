<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlokRequest extends FormRequest
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
        $rentalId = $this->user()->rental?->id;

        return [
            'unit_id' => ['required', 'integer', Rule::exists('unit', 'id')->where(fn ($q) => $q->where('rental_id', $rentalId))],
            'mulai' => ['required', 'date'],
            'selesai' => ['required', 'date', 'after:mulai'],
            'alasan' => ['nullable', 'string', 'max:120'],
            'paksa' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit_id.exists' => 'Pilih unit milik rental kamu.',
            'selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ];
    }
}
