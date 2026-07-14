<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::in(['email', 'whatsapp'])],
            'identifier' => ['required', 'string', 'max:190'],
            'code' => ['required', 'digits:6'],
        ];
    }
}
