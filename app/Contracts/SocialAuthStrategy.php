<?php

namespace App\Contracts;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

interface SocialAuthStrategy
{
    /**
     * Get the OAuth redirect URL
     *
     * @return string
     */
    public function getRedirectUrl(): string;

    /**
     * Handle OAuth callback and authenticate user
     *
     * @param string $code
     * @return array
     */
    public function handleCallback(string $code): array;

    /**
     * Find or create user from social account data
     *
     * @param SocialiteUser $socialUser
     * @return User
     */
    public function findOrCreateUser(SocialiteUser $socialUser): User;
}
