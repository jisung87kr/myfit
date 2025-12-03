<?php

namespace App\Enums;

enum CacheKey: string
{
    case USER_PROFILE = 'user:profile';
    case USER_DIET_PLAN = 'user:diet_plan';
    case SURVEY_RESPONSE = 'survey:response';
    case DAILY_RECORD = 'daily:record';

    /**
     * Generate cache key with parameters
     */
    public function key(mixed ...$params): string
    {
        $suffix = implode(':', $params);
        return "{$this->value}:{$suffix}";
    }

    /**
     * Get cache TTL in seconds
     */
    public function ttl(): int
    {
        return match($this) {
            self::USER_PROFILE => 3600, // 1 hour
            self::USER_DIET_PLAN => 1800, // 30 minutes
            self::SURVEY_RESPONSE => 7200, // 2 hours
            self::DAILY_RECORD => 600, // 10 minutes
        };
    }
}
