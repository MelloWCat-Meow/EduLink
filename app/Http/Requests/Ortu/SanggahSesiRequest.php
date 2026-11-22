<?php

namespace App\Http\Requests\Ortu;

use Illuminate\Foundation\Http\FormRequest;

class SanggahSesiRequest extends FormRequest
{
    /**
     * Errors go to their own bag, so they show under the session they belong to.
     */
    protected $errorBag = 'sanggahan';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return ['alasan' => ['required', 'string', 'min:10', 'max:255']];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alasan.required' => 'Tulis alasan sanggahan, misalnya tutor tidak datang atau sesi tidak sesuai.',
            'alasan.min' => 'Ceritakan sedikit lebih lengkap, minimal 10 karakter.',
            'alasan.max' => 'Alasan terlalu panjang, maksimal 255 karakter.',
        ];
    }
}
