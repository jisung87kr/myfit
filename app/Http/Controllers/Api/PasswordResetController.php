<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Password\ResetPasswordRequest;
use App\Http\Requests\Password\SendResetLinkRequest;
use App\Services\PasswordResetService;

class PasswordResetController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {}

    /**
     * Send password reset link email
     */
    public function sendResetLink(SendResetLinkRequest $request)
    {
        $this->passwordResetService->sendResetLink($request->email);

        return response()->success(
            null,
            '비밀번호 재설정 링크가 이메일로 전송되었습니다.'
        );
    }

    /**
     * Reset password
     */
    public function reset(ResetPasswordRequest $request)
    {
        $this->passwordResetService->resetPassword(
            $request->email,
            $request->token,
            $request->password
        );

        return response()->success(
            null,
            '비밀번호가 성공적으로 변경되었습니다. 새 비밀번호로 로그인해주세요.'
        );
    }
}
