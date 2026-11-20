<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class AktivasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['kontak' => mb_strtolower(trim((string) $this->input('kontak')))]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'kontak' => ['required', 'string', 'email', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kontak.required' => 'Email belum diisi.',
            'kontak.email' => 'Tulis email lengkap, misalnya nama@contoh.com.',
            'kontak.max' => 'Email terlalu panjang, maksimal 50 karakter.',
        ];
    }
}
