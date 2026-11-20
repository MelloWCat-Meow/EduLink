<?php

namespace App\Http\Requests\Ortu;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * A review is allowed once per order, and only after the order is finished.
 */
class UlasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nilai' => ['required', 'integer', 'between:1,5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Pesanan $pesanan */
                $pesanan = $this->route('pesanan');

                if ($pesanan->status !== StatusPesanan::Selesai) {
                    $validator->errors()->add('nilai', 'Ulasan baru bisa diberikan setelah pesanan selesai.');
                } elseif ($pesanan->ulasan()->exists()) {
                    $validator->errors()->add('nilai', 'Pesanan ini sudah Anda ulas. Ulasan hanya bisa diberikan sekali.');
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
            'nilai.required' => 'Pilih nilai 1 sampai 5.',
            'nilai.integer' => 'Pilih nilai 1 sampai 5.',
            'nilai.between' => 'Pilih nilai 1 sampai 5.',
            'komentar.max' => 'Komentar terlalu panjang, maksimal 1.000 karakter.',
        ];
    }
}
