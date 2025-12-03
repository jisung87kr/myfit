<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
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
            'photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048', // 2MB
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'photo.required' => '프로필 사진을 선택해주세요.',
            'photo.image' => '이미지 파일만 업로드 가능합니다.',
            'photo.mimes' => '지원하는 이미지 형식: jpeg, jpg, png, gif, webp',
            'photo.max' => '이미지 크기는 2MB를 초과할 수 없습니다.',
        ];
    }
}
