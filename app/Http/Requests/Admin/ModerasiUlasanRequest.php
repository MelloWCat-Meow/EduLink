<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ModerasiUlasanRequest extends FormRequest
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
            'ditampilkan' => ['required', 'boolean'],
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ditampilkan.required' => 'Pilih tampilkan atau sembunyikan.',
            'alasan.required' => 'Tulis alasan moderasi.',
            'alasan.min' => 'Alasan terlalu singkat, minimal 5 karakter.',
            'alasan.max' => 'Alasan terlalu panjang, maksimal 255 karakter.',
        ];
    }
}
