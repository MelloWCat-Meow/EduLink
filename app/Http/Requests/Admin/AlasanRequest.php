<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AlasanRequest extends FormRequest
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
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alasan.required' => 'Tulis alasannya. Alasan ini dicatat.',
            'alasan.min' => 'Alasan terlalu singkat, minimal 5 karakter.',
            'alasan.max' => 'Alasan terlalu panjang, maksimal 255 karakter.',
        ];
    }
}
