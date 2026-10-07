<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'otp' => ['required', 'string', 'size:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'otp.*' => 'ワンタイムパスワードが間違っているか、有効期限が切れています',
        ];
    }
}
