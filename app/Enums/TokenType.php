<?php

namespace App\Enums;

enum TokenType: string
{
    case AUTH = 'auth_token';
    case REFRESH = 'refresh_token';
    case PASSWORD_RESET = 'password_reset';
    case EMAIL_VERIFICATION = 'email_verification';
    case PHONE_VERIFICATION = 'phone_verification';

    /**
     * Get token expiration time in minutes
     */
    public function expiresIn(): ?int
    {
        return match($this) {
            self::AUTH => config('sanctum.expiration', 60 * 24), // 24 hours
            self::REFRESH => 60 * 24 * 30, // 30 days
            self::PASSWORD_RESET => 60, // 1 hour
            self::EMAIL_VERIFICATION => 60 * 24, // 24 hours
            self::PHONE_VERIFICATION => 10, // 10 minutes
        };
    }

    /**
     * Get token description
     */
    public function description(): string
    {
        return match($this) {
            self::AUTH => '인증 토큰',
            self::REFRESH => '리프레시 토큰',
            self::PASSWORD_RESET => '비밀번호 재설정 토큰',
            self::EMAIL_VERIFICATION => '이메일 인증 토큰',
            self::PHONE_VERIFICATION => '전화번호 인증 토큰',
        };
    }
}
