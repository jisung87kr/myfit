<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)/',
                'different:current_password',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'current_password.required' => '현재 비밀번호를 입력해주세요.',
            'new_password.required' => '새 비밀번호를 입력해주세요.',
            'new_password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
            'new_password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'new_password.regex' => '비밀번호는 영문과 숫자를 포함해야 합니다.',
            'new_password.different' => '새 비밀번호는 현재 비밀번호와 달라야 합니다.',
        ];
    }
}
