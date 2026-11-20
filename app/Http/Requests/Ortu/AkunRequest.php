<?php

namespace App\Http\Requests\Ortu;

use App\Http\Requests\Auth\DaftarRequest;
use Illuminate\Foundation\Http\FormRequest;

class AkunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => trim((string) $this->input('nama')),
            'no_hp' => preg_replace('/[\s\-().]/', '', (string) $this->input('no_hp')),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'regex:'.DaftarRequest::POLA_NO_HP],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama belum diisi.',
            'nama.max' => 'Nama terlalu panjang, maksimal 100 karakter.',
            'no_hp.required' => 'Nomor HP belum diisi.',
            'no_hp.regex' => 'Tulis nomor HP Indonesia, misalnya 0812 3456 7890.',
        ];
    }
}
