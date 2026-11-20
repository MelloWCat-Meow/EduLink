<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class RekeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rekening_bank' => trim((string) $this->input('rekening_bank')),
            'rekening_nomor' => preg_replace('/[\s\-.]/', '', (string) $this->input('rekening_nomor')),
            'rekening_atas_nama' => trim((string) $this->input('rekening_atas_nama')),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'rekening_bank' => ['required', 'string', 'max:50'],
            'rekening_nomor' => ['required', 'regex:/^\d{6,20}$/'],
            'rekening_atas_nama' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rekening_bank.required' => 'Tulis nama bank.',
            'rekening_nomor.required' => 'Tulis nomor rekening.',
            'rekening_nomor.regex' => 'Nomor rekening berisi 6 sampai 20 angka.',
            'rekening_atas_nama.required' => 'Tulis nama pemilik rekening, sesuai buku tabungan.',
        ];
    }
}
