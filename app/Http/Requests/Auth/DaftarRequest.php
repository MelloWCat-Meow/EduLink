<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class DaftarRequest extends FormRequest
{
    /**
     * Indonesian mobile number after spaces and dashes are removed.
     */
    public const POLA_NO_HP = '/^(\+62|62|0)8[0-9]{7,12}$/';

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
            'no_hp' => preg_replace('/[\s\-().]/', '', (string) $this->input('no_hp')),
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
            // The tutor uses it for WhatsApp once an order is paid.
            'no_hp' => ['required', 'string', 'regex:'.self::POLA_NO_HP],
            'password' => ['required', 'string', Password::min(8)],
            // Checked on its own field so the message sits under "Ulangi kata sandi".
            'password_confirmation' => ['required', 'string', 'same:password'],
            'setuju' => ['accepted'],
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
            'no_hp.required' => 'Nomor HP belum diisi.',
            'no_hp.regex' => 'Tulis nomor HP Indonesia, misalnya 0812 3456 7890.',
            'password.required' => 'Kata sandi belum diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password_confirmation.required' => 'Ulangi kata sandi di sini.',
            'password_confirmation.same' => 'Ulangan kata sandi belum sama dengan kata sandi di atas.',
            'setuju.accepted' => 'Centang persetujuan Syarat dan Ketentuan serta Kebijakan Privasi untuk melanjutkan.',
        ];
    }
}
