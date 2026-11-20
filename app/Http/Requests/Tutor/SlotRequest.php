<?php

namespace App\Http\Requests\Tutor;

use App\Enums\Hari;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One weekly slot from the tutor dashboard.
 */
class SlotRequest extends FormRequest
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
            'hari' => ['required', Rule::enum(Hari::class)],
            'jam' => ['required', 'date_format:H:i', 'after_or_equal:06:00', 'before_or_equal:21:00'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hari.required' => 'Pilih hari.',
            'hari.enum' => 'Pilih hari dari daftar.',
            'jam.required' => 'Tulis jam mulai.',
            'jam.date_format' => 'Tulis jam seperti 16:00.',
            'jam.after_or_equal' => 'Jam mulai paling awal 06:00.',
            'jam.before_or_equal' => 'Jam mulai paling lambat 21:00.',
        ];
    }
}
