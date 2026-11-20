<?php

namespace App\Http\Requests\Ortu;

use Illuminate\Foundation\Http\FormRequest;

class SiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nama' => trim((string) $this->input('nama'))]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'jenjang' => ['required', 'in:SD,SMP,SMA'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama anak belum diisi.',
            'nama.max' => 'Nama terlalu panjang, maksimal 100 karakter.',
            'jenjang.required' => 'Pilih jenjang sekolah anak.',
            'jenjang.in' => 'Pilih SD, SMP, atau SMA.',
        ];
    }
}
