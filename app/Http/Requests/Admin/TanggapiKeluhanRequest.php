<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusKeluhan;
use App\Models\Keluhan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Complaints move forward only (baru -> diproses -> selesai), and every
 * change carries a reply the parent can read.
 */
class TanggapiKeluhanRequest extends FormRequest
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
            'status' => ['required', Rule::enum(StatusKeluhan::class)],
            'tanggapan_admin' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Keluhan $keluhan */
                $keluhan = $this->route('keluhan');
                $ke = StatusKeluhan::tryFrom((string) $this->input('status'));

                if ($ke !== null && $ke !== $keluhan->status && ! $keluhan->status->canMoveTo($ke)) {
                    $validator->errors()->add('status', "Keluhan berstatus \"{$keluhan->status->label()}\" tidak bisa kembali ke \"{$ke->label()}\".");
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Pilih status keluhan.',
            'status.enum' => 'Pilih status dari daftar.',
            'tanggapan_admin.required' => 'Tulis tanggapan untuk orang tua.',
            'tanggapan_admin.min' => 'Tanggapan terlalu singkat, minimal 5 karakter.',
            'tanggapan_admin.max' => 'Tanggapan terlalu panjang, maksimal 2.000 karakter.',
        ];
    }
}
