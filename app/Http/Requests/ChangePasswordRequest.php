<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->max(32)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'current_password' => '現在のパスワード',
            'password' => '新しいパスワード',
            'password_confirmation' => '新しいパスワード（確認）',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => ':attributeを入力してください。',
            'current_password.current_password' => ':attributeが正しくありません。',
            'password.required' => ':attributeを入力してください。',
            'password.confirmed' => ':attributeと新しいパスワード（確認）が一致しません。',
            'password.min' => ':attributeは:min文字以上で入力してください。',
            'password.max' => ':attributeは:max文字以下で入力してください。',
            'password.password.mixed' => ':attributeには大文字と小文字を含めてください。',
            'password.password.numbers' => ':attributeには数字を含めてください。',
            'password.password.symbols' => ':attributeには記号を含めてください。',
        ];
    }
}
