<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)/',
            ],
        ], [
            'password.regex' => '비밀번호는 영문과 숫자를 포함해야 합니다.',
        ]);

        $result = $this->authService->register($validated);

        return response()->created($result, '회원가입이 완료되었습니다.');
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->login(
                $request->email,
                $request->password
            );

            return response()->success($result, '로그인 성공');
        } catch (AuthenticationException $e) {
            throw ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->success(null, '로그아웃되었습니다.');
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $userInfo = $this->authService->getUserInfo($request->user());

        return response()->success($userInfo);
    }
}
