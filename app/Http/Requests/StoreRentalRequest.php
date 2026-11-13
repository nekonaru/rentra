<?php

namespace App\Http\Requests;

use App\Support\ReservedSlugs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'not_in:'.implode(',', ReservedSlugs::list()), Rule::unique('rentals', 'slug')],
            'no_wa' => ['required', 'regex:/^62[0-9]{8,14}$/'],
            'info_rekening' => ['nullable', 'string', 'max:500'],
            'durasi_hold_menit' => ['required', 'integer', 'min:5', 'max:1440'],
            'persen_dp' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->user()->rental()->exists()) {
                $validator->errors()->add('nama', 'Kamu sudah punya rental.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung.',
            'slug.not_in' => 'Slug ini dipakai sistem, pilih yang lain.',
            'no_wa.regex' => 'Nomor WA harus format 62 diikuti angka, tanpa spasi atau tanda +.',
        ];
    }
}
