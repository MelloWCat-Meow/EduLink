<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MapelTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'mapel' => ['required', 'array', 'min:1'],
            'mapel.*' => ['string', 'distinct', Rule::exists('mata_pelajaran', 'kode_mapel')],
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mapel.required' => 'Pilih minimal satu mata pelajaran.',
            'mapel.min' => 'Pilih minimal satu mata pelajaran.',
            'mapel.*.exists' => 'Mata pelajaran itu tidak ada di daftar.',
            'alasan.required' => 'Tulis alasannya. Alasan ini dicatat dan dikirim ke tutor.',
            'alasan.min' => 'Alasan terlalu singkat, minimal 5 karakter.',
        ];
    }
}
