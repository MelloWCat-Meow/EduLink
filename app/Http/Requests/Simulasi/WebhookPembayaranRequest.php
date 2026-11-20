<?php

namespace App\Http\Requests\Simulasi;

use App\Support\TandaTanganWebhook;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class WebhookPembayaranRequest extends FormRequest
{
    /**
     * The gateway is not a signed-in user; the HMAC signature is the authorization.
     */
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
            'payment_id' => ['required', 'string', 'max:10'],
            'order_id' => ['required', 'string', 'max:15'],
            'status' => ['required', 'in:berhasil,gagal'],
            'signature' => ['required', 'string'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! TandaTanganWebhook::cocok($this->string('signature'), $this->string('payment_id'), $this->string('order_id'), $this->string('status'))) {
                    $validator->errors()->add('signature', 'Tanda tangan webhook tidak cocok.');
                }
            },
        ];
    }
}
