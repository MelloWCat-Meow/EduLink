<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class PencairanRequest extends FormRequest
{
    public const NOMINAL_MIN = 10000;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nominal' => preg_replace('/\D/', '', (string) $this->input('nominal'))]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'periode' => ['required', 'date_format:Y-m', 'before_or_equal:'.now()->format('Y-m')],
            'nominal' => ['required', 'integer', 'min:'.self::NOMINAL_MIN],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'periode.required' => 'Pilih periode pendapatan.',
            'periode.date_format' => 'Pilih periode seperti 2026-11.',
            'periode.before_or_equal' => 'Periode tidak boleh bulan yang belum berjalan.',
            'nominal.required' => 'Tulis nominal yang dicairkan.',
            'nominal.integer' => 'Tulis nominal dengan angka saja.',
            'nominal.min' => 'Nominal minimal Rp10.000.',
        ];
    }
}
