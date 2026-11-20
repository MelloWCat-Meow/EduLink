<?php

namespace App\Http\Requests\Tutor;

use App\Enums\Hari;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Step 4: weekly slots as rows of day + start time. Blank rows are ignored.
 */
class JadwalRequest extends FormRequest
{
    public const SLOT_MAKS = 21;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slot = collect($this->input('slot', []))
            ->filter(fn ($baris): bool => is_array($baris) && (filled($baris['hari'] ?? null) || filled($baris['jam'] ?? null)))
            ->values()
            ->all();

        $this->merge(['slot' => $slot]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'slot' => ['required', 'array', 'min:1', 'max:'.self::SLOT_MAKS],
            'slot.*.hari' => ['required', Rule::enum(Hari::class)],
            'slot.*.jam' => ['required', 'date_format:H:i', 'after_or_equal:06:00', 'before_or_equal:21:00'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $kunci = collect($this->input('slot'))->map(fn (array $baris): string => $baris['hari'].' '.$baris['jam']);

            if ($kunci->duplicates()->isNotEmpty()) {
                $validator->errors()->add('slot', 'Ada jadwal yang ditulis dua kali. Hapus salah satunya.');
            }
        }];
    }

    /**
     * @return list<array{hari: string, jam: string}>
     */
    public function slot(): array
    {
        return array_map(fn (array $baris): array => ['hari' => $baris['hari'], 'jam' => $baris['jam']], $this->validated('slot'));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slot.required' => 'Tambahkan minimal satu jadwal mingguan.',
            'slot.min' => 'Tambahkan minimal satu jadwal mingguan.',
            'slot.max' => 'Paling banyak '.self::SLOT_MAKS.' jadwal.',
            'slot.*.hari.required' => 'Pilih hari untuk jadwal ini.',
            'slot.*.hari.enum' => 'Pilih hari dari daftar.',
            'slot.*.jam.required' => 'Tulis jam mulai untuk jadwal ini.',
            'slot.*.jam.date_format' => 'Tulis jam seperti 16:00.',
            'slot.*.jam.after_or_equal' => 'Jam mulai paling awal 06:00.',
            'slot.*.jam.before_or_equal' => 'Jam mulai paling lambat 21:00.',
        ];
    }
}
