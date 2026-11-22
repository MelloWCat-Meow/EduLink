<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class TautanMeetingRequest extends FormRequest
{
    public const PANJANG_MAKS = 255;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['tautan_meeting' => trim((string) $this->input('tautan_meeting')) ?: null]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'tautan_meeting' => ['nullable', 'string', 'max:'.self::PANJANG_MAKS, 'url:https', 'starts_with:https://'],
        ];
    }

    public function tautan(): ?string
    {
        return $this->validated('tautan_meeting');
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tautan_meeting.url' => 'Tulis tautan lengkap yang diawali https://, misalnya https://meet.google.com/abc-defg-hij.',
            'tautan_meeting.starts_with' => 'Tautan harus diawali https://.',
            'tautan_meeting.max' => 'Tautan terlalu panjang, maksimal '.self::PANJANG_MAKS.' karakter.',
        ];
    }
}
