<?php

namespace App\Enums;

enum SocialProvider: string
{
    case GOOGLE = 'google';
    case KAKAO = 'kakao';
    case NAVER = 'naver';
    case APPLE = 'apple';

    /**
     * Get display name
     */
    public function displayName(): string
    {
        return match($this) {
            self::GOOGLE => 'Google',
            self::KAKAO => 'Kakao',
            self::NAVER => 'Naver',
            self::APPLE => 'Apple',
        };
    }

    /**
     * Get OAuth configuration key
     */
    public function configKey(): string
    {
        return "services.{$this->value}";
    }

    /**
     * Get all available providers
     */
    public static function available(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Check if provider is enabled in config
     */
    public function isEnabled(): bool
    {
        return config("{$this->configKey()}.enabled", false);
    }
}
