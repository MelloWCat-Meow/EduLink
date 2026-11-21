<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class BuatSandiRequest extends FormRequest
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
            'password' => ['required', 'string', Password::min(8)],
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
            'password.required' => 'Kata sandi belum diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password_confirmation.required' => 'Ulangi kata sandi di sini.',
            'password_confirmation.same' => 'Ulangan kata sandi belum sama dengan kata sandi di atas.',
            'setuju.accepted' => 'Centang persetujuan Syarat dan Ketentuan serta Kebijakan Privasi untuk melanjutkan.',
        ];
    }
}
