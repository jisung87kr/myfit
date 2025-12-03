<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadPhotoRequest;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    /**
     * Get user profile
     */
    public function show(Request $request)
    {
        $profile = $this->profileService->getProfile($request->user());

        return response()->success($profile);
    }

    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request)
    {
        $profile = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return response()->success($profile, '프로필이 수정되었습니다.');
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $this->profileService->changePassword(
            $request->user(),
            $request->current_password,
            $request->new_password
        );

        return response()->success(null, '비밀번호가 변경되었습니다. 다시 로그인해주세요.');
    }

    /**
     * Upload profile photo
     */
    public function uploadPhoto(UploadPhotoRequest $request)
    {
        $profile = $this->profileService->uploadPhoto(
            $request->user(),
            $request->file('photo')
        );

        return response()->success($profile, '프로필 사진이 업로드되었습니다.');
    }

    /**
     * Delete profile photo
     */
    public function deletePhoto(Request $request)
    {
        $this->profileService->deletePhoto($request->user());

        return response()->success(null, '프로필 사진이 삭제되었습니다.');
    }
}
