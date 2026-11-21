<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class GantiKataSandiRequest extends FormRequest
{
    /**
     * Errors go to their own bag, so they never mix with the profile form on the same page.
     */
    protected $errorBag = 'kataSandi';

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
            'kata_sandi_lama' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::min(8), 'different:kata_sandi_lama'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kata_sandi_lama.required' => 'Tulis kata sandi lama.',
            'kata_sandi_lama.current_password' => 'Kata sandi lama belum cocok.',
            'password.required' => 'Kata sandi baru belum diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang lama.',
            'password_confirmation.required' => 'Ulangi kata sandi baru.',
            'password_confirmation.same' => 'Ulangan kata sandi belum sama dengan kata sandi baru.',
        ];
    }
}
