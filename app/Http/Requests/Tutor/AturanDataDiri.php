<?php

namespace App\Http\Requests\Tutor;

use App\Http\Requests\Auth\DaftarRequest;

/**
 * Name and phone rules shared by the sign-up step and the "Data diri" step.
 */
trait AturanDataDiri
{
    /**
     * Phone numbers are stored as digits, e.g. "0812 6610-0201" becomes "081266100201".
     */
    protected function rapikanDataDiri(): void
    {
        $this->merge([
            'nama' => trim((string) $this->input('nama')),
            'no_hp' => preg_replace('/[\s\-().]/', '', (string) $this->input('no_hp')),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function aturanDataDiri(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'regex:'.DaftarRequest::POLA_NO_HP],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function pesanDataDiri(): array
    {
        return [
            'nama.required' => 'Nama belum diisi.',
            'nama.max' => 'Nama terlalu panjang, maksimal 100 karakter.',
            'no_hp.required' => 'Nomor HP belum diisi.',
            'no_hp.regex' => 'Tulis nomor HP Indonesia, misalnya 0812 3456 7890.',
        ];
    }
}
