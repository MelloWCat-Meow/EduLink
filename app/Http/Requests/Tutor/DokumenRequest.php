<?php

namespace App\Http\Requests\Tutor;

use App\Enums\JenisDokumen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

/**
 * One verification document. The type is read from the file's content
 * (mimes and mimetypes use finfo), not from its name.
 */
class DokumenRequest extends FormRequest
{
    public const UKURAN_MAKS_KB = 2048;

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
            'jenis' => ['required', Rule::enum(JenisDokumen::class)],
            'berkas' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(self::UKURAN_MAKS_KB),
                'mimetypes:application/pdf,image/jpeg,image/png',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenis.required' => 'Pilih jenis dokumen.',
            'jenis.enum' => 'Jenis dokumen tidak dikenal.',
            'berkas.required' => 'Pilih berkas yang akan diunggah.',
            'berkas.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya tidak lebih dari 2 MB, lalu coba lagi.',
            'berkas.mimes' => 'Berkas harus PDF, JPG, atau PNG.',
            'berkas.mimetypes' => 'Isi berkas bukan PDF, JPG, atau PNG. Mengganti nama berkas tidak cukup.',
            'berkas.max' => 'Ukuran berkas maksimal 2 MB.',
        ];
    }
}
