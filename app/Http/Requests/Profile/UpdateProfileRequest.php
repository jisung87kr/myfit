<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20|regex:/^[0-9-]+$/',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => '이름은 문자열이어야 합니다.',
            'name.max' => '이름은 255자를 초과할 수 없습니다.',
            'phone.regex' => '전화번호 형식이 올바르지 않습니다.',
            'phone.max' => '전화번호는 20자를 초과할 수 없습니다.',
        ];
    }
}
