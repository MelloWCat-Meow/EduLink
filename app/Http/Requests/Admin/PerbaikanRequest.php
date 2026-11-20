<?php

namespace App\Http\Requests\Admin;

use App\Enums\LangkahPendaftaran;
use App\Models\Tutor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * "Minta perbaikan": a general note, plus the sections and documents the
 * tutor has to fix, each with its own note.
 */
class PerbaikanRequest extends FormRequest
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
        /** @var Tutor $tutor */
        $tutor = $this->route('tutor');

        return [
            'catatan' => ['required', 'string', 'min:5', 'max:1000'],
            'bagian' => ['array'],
            'bagian.*' => [Rule::in(array_map(fn (LangkahPendaftaran $langkah): string => $langkah->value, LangkahPendaftaran::bagian()))],
            'catatan_bagian' => ['array'],
            'catatan_bagian.*' => ['nullable', 'string', 'max:255'],
            'dokumen' => ['array'],
            'dokumen.*' => [Rule::exists('dokumen_verifikasi', 'id_dokumen')->where('id_tutor', $tutor->id_tutor)],
            'alasan_dokumen' => ['array'],
            'alasan_dokumen.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('bagian', []) === [] && $this->input('dokumen', []) === []) {
                $validator->errors()->add('bagian', 'Tandai minimal satu bagian atau dokumen yang perlu diperbaiki.');
            }

            foreach ($this->input('dokumen', []) as $id) {
                if (blank($this->input("alasan_dokumen.$id"))) {
                    $validator->errors()->add("alasan_dokumen.$id", 'Tulis apa yang salah dengan dokumen ini.');
                }
            }
        }];
    }

    /**
     * @return array<string, string|null>
     */
    public function bagian(): array
    {
        return collect($this->validated('bagian', []))
            ->mapWithKeys(fn (string $kunci): array => [$kunci => $this->validated("catatan_bagian.$kunci")])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function dokumen(): array
    {
        return collect($this->validated('dokumen', []))
            ->mapWithKeys(fn (string $id): array => [$id => (string) $this->validated("alasan_dokumen.$id")])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'catatan.required' => 'Tulis catatan untuk tutor. Catatan ini dikirim lewat email.',
            'catatan.min' => 'Catatan terlalu singkat, minimal 5 karakter.',
            'catatan_bagian.*.max' => 'Catatan per bagian maksimal 255 karakter.',
            'alasan_dokumen.*.max' => 'Alasan maksimal 255 karakter.',
        ];
    }
}
