<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetKataSandiRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'kontak' => ['required', 'string', 'email', 'max:50'],
            'password' => ['required', 'string', Password::min(8)],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Tautan tidak lengkap. Minta tautan baru.',
            'kontak.required' => 'Email belum diisi.',
            'kontak.email' => 'Tulis email lengkap, misalnya nama@contoh.com.',
            'password.required' => 'Kata sandi baru belum diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password_confirmation.required' => 'Ulangi kata sandi di sini.',
            'password_confirmation.same' => 'Ulangan kata sandi belum sama dengan kata sandi di atas.',
        ];
    }
}
