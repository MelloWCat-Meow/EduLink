<?php

namespace App\Http\Requests\Tutor;

use App\Http\Requests\Auth\DaftarRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

/**
 * Profile fields a verified tutor may change without a new verification.
 * Subjects and levels are not accepted here.
 */
class ProfilAktifRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tarif' => preg_replace('/\D/', '', (string) $this->input('tarif')),
            'no_hp' => preg_replace('/[\s\-().]/', '', (string) $this->input('no_hp')),
            'kompetensi' => trim((string) $this->input('kompetensi')),
            'pengalaman' => trim((string) $this->input('pengalaman')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'tarif' => ['required', 'integer', 'between:'.ProfilTutorRequest::TARIF_MIN.','.ProfilTutorRequest::TARIF_MAKS],
            'lokasi' => ['required', Rule::in(config('landing.locations'))],
            'layanan' => ['required', 'array', 'min:1'],
            'layanan.*' => [Rule::in(['online', 'kunjungan_rumah'])],
            'kompetensi' => ['required', 'string', 'min:20', 'max:1000'],
            'pengalaman' => ['required', 'string', 'min:20', 'max:1000'],
            'no_hp' => ['required', 'string', 'regex:'.DaftarRequest::POLA_NO_HP],
            'foto' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png'])->max(2048)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new ProfilTutorRequest)->messages() + [
            'no_hp.required' => 'Nomor HP belum diisi.',
            'no_hp.regex' => 'Tulis nomor HP Indonesia, misalnya 0812 3456 7890.',
        ];
    }
}
