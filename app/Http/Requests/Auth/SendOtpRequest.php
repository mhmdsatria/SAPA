<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::in(['email', 'whatsapp'])],
            'identifier' => [
                'required',
                'string',
                'max:190',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->input('channel') === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Alamat email tidak valid.');
                    }

                    if ($this->input('channel') === 'whatsapp' && strlen(preg_replace('/\D+/', '', (string) $value) ?? '') < 9) {
                        $fail('Nomor WhatsApp tidak valid.');
                    }
                },
            ],
        ];
    }
}
