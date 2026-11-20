<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class DaftarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Emails are compared in lowercase so "Nama@Contoh.com" cannot register twice.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => trim((string) $this->input('nama')),
            'kontak' => mb_strtolower(trim((string) $this->input('kontak'))),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'kontak' => ['required', 'string', 'email', 'max:50', 'unique:akun_pengguna,kontak'],
            'password' => ['required', 'string', Password::min(8)],
            // Checked on its own field so the message sits under "Ulangi kata sandi".
            'password_confirmation' => ['required', 'string', 'same:password'],
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
            'kontak.required' => 'Email belum diisi.',
            'kontak.email' => 'Tulis email lengkap, misalnya nama@contoh.com.',
            'kontak.max' => 'Email terlalu panjang, maksimal 50 karakter.',
            'kontak.unique' => 'Email ini sudah terdaftar. Silakan masuk dengan email tersebut.',
            'password.required' => 'Kata sandi belum diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password_confirmation.required' => 'Ulangi kata sandi di sini.',
            'password_confirmation.same' => 'Ulangan kata sandi belum sama dengan kata sandi di atas.',
        ];
    }
}
