<?php

namespace App\Http\Requests\Auth;

use App\Enums\Peran;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MasukRequest extends FormRequest
{
    /**
     * Failed attempts allowed per email and IP address before a lockout.
     */
    public const MAX_ATTEMPTS = 5;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'kontak' => ['required', 'string', 'email', 'max:50'],
            'password' => ['required', 'string'],
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
            'password.required' => 'Kata sandi belum diisi.',
        ];
    }

    /**
     * Sign in with the email (kontak) and password, counting failed attempts.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = ['kontak' => Str::lower($this->string('kontak')), 'password' => $this->string('password')->toString()];

        if (! Auth::attempt($credentials, $this->boolean('ingat'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'kontak' => 'Email atau kata sandi belum cocok. Periksa lagi, lalu coba masuk.',
            ]);
        }

        if (! in_array(Auth::user()->peran, [Peran::Ortu, Peran::Tutor, Peran::Admin], true)) {
            Auth::logout();

            throw ValidationException::withMessages([
                'kontak' => 'Akun ini belum bisa masuk lewat halaman ini.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'kontak' => 'Terlalu banyak percobaan masuk. Coba lagi dalam '.ceil($seconds / 60).' menit.',
        ]);
    }

    /**
     * Keyed by email and IP so one person cannot lock out an account from everywhere.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('kontak')).'|'.$this->ip());
    }
}
