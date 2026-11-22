<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class DataDiriRequest extends FormRequest
{
    use AturanDataDiri;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->rapikanDataDiri();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return $this->aturanDataDiri();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->pesanDataDiri();
    }
}
