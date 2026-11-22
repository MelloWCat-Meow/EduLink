<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class AjukanJadwalUlangRequest extends FormRequest
{
    /**
     * Errors go to their own bag, so they show next to the session they belong to.
     */
    protected $errorBag = 'jadwalUlang';

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
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam' => ['required', 'date_format:H:i', 'after_or_equal:06:00', 'before_or_equal:21:00'],
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    public function waktuBaru(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', $this->validated('tanggal').' '.$this->validated('jam'))->startOfMinute();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal.required' => 'Pilih tanggal baru.',
            'tanggal.date_format' => 'Pilih tanggal dari kalender.',
            'jam.required' => 'Tulis jam mulai baru.',
            'jam.date_format' => 'Tulis jam seperti 16:00.',
            'jam.after_or_equal' => 'Jam mulai paling awal 06:00.',
            'jam.before_or_equal' => 'Jam mulai paling lambat 21:00.',
            'alasan.required' => 'Tulis alasan jadwal ulang.',
            'alasan.min' => 'Alasan terlalu singkat, minimal 5 karakter.',
        ];
    }
}
