<?php

namespace App\Http\Requests\Ortu;

use App\Enums\JenisKeluhan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KeluhanRequest extends FormRequest
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
            'jenis' => ['required', Rule::enum(JenisKeluhan::class)],
            'deskripsi' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenis.required' => 'Pilih jenis keluhan.',
            'jenis.enum' => 'Pilih jenis keluhan dari daftar.',
            'deskripsi.required' => 'Ceritakan apa yang terjadi.',
            'deskripsi.min' => 'Ceritakan sedikit lebih lengkap, minimal 10 karakter.',
            'deskripsi.max' => 'Cerita terlalu panjang, maksimal 2.000 karakter.',
        ];
    }
}
