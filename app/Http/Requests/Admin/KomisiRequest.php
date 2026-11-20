<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KomisiRequest extends FormRequest
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
            'persentase' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'persentase.required' => 'Isi persentase komisi.',
            'persentase.numeric' => 'Persentase harus berupa angka, misalnya 15 atau 12.5.',
            'persentase.decimal' => 'Paling banyak dua angka di belakang koma.',
            'persentase.min' => 'Persentase tidak boleh negatif.',
            'persentase.max' => 'Persentase paling tinggi 50.',
        ];
    }
}
