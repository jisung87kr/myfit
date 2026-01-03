<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {}

    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $this->passwordResetService->sendResetLink($request->email);

            return response()->json([
                'success' => true,
                'message' => '비밀번호 재설정 링크를 이메일로 발송했습니다.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '이메일 발송 중 오류가 발생했습니다.',
            ], 500);
        }
    }
}
