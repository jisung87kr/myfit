<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /**
     * Send password reset link to user's email
     *
     * @param string $email
     * @return string
     * @throws ValidationException
     */
    public function sendResetLink(string $email): string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['해당 이메일로 가입된 계정을 찾을 수 없습니다.'],
            ]);
        }

        // Generate reset token
        $token = Password::createToken($user);

        // Send notification
        $user->notify(new ResetPasswordNotification($token));

        return $token;
    }

    /**
     * Reset user password with token
     *
     * @param string $email
     * @param string $token
     * @param string $password
     * @return void
     * @throws ValidationException
     */
    public function resetPassword(string $email, string $token, string $password): void
    {
        $status = Password::reset(
            ['email' => $email, 'password' => $password, 'token' => $token],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all existing tokens for security
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $message = match ($status) {
                Password::INVALID_TOKEN => '비밀번호 재설정 링크가 유효하지 않거나 만료되었습니다.',
                Password::INVALID_USER => '해당 이메일로 가입된 계정을 찾을 수 없습니다.',
                default => '비밀번호 재설정에 실패했습니다.',
            };

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }
    }

    /**
     * Verify reset token
     *
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function verifyToken(string $email, string $token): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        return Password::tokenExists($user, $token);
    }
}
