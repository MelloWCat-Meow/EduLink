<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Step 1 of the external tutor sign-up: the account itself.
 */
class DaftarTutorRequest extends FormRequest
{
    use AturanDataDiri;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->rapikanDataDiri();
        $this->merge(['kontak' => mb_strtolower(trim((string) $this->input('kontak')))]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->aturanDataDiri() + [
            // Also unique among tutor records, so an internal tutor's email cannot be taken by a new sign-up.
            'kontak' => ['required', 'string', 'email', 'max:50', 'unique:akun_pengguna,kontak', 'unique:tutor,kontak'],
            'password' => ['required', 'string', Password::min(8)],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->pesanDataDiri() + [
            'kontak.required' => 'Email belum diisi.',
            'kontak.email' => 'Tulis email lengkap, misalnya nama@contoh.com.',
            'kontak.max' => 'Email terlalu panjang, maksimal 50 karakter.',
            'kontak.unique' => 'Email ini sudah terdaftar. Masuk dengan email tersebut, atau aktifkan akun kalau Anda pengajar bimbel EduLink.',
            'password.required' => 'Kata sandi belum diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password_confirmation.required' => 'Ulangi kata sandi di sini.',
            'password_confirmation.same' => 'Ulangan kata sandi belum sama dengan kata sandi di atas.',
        ];
    }
}
