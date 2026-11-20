<?php

namespace App\Http\Requests\Ortu;

use App\Enums\MetodePembayaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BayarRequest extends FormRequest
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
            'metode' => ['required', Rule::enum(MetodePembayaran::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'metode.required' => 'Pilih cara bayar.',
            'metode.enum' => 'Pilih cara bayar dari daftar.',
        ];
    }
}
