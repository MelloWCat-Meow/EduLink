<?php

namespace App\Http\Requests\Tutor;

use App\Enums\Kehadiran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatatSesiRequest extends FormRequest
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
            'kehadiran' => ['required', Rule::enum(Kehadiran::class)],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kehadiran.required' => 'Pilih hadir atau tidak hadir.',
            'kehadiran.enum' => 'Pilih hadir atau tidak hadir.',
            'catatan.max' => 'Catatan maksimal 1.000 karakter.',
        ];
    }
}
