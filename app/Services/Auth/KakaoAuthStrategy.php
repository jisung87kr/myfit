<?php

namespace App\Services\Auth;

use App\Contracts\SocialAuthStrategy;
use App\Enums\SocialProvider;
use App\Enums\TokenType;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class KakaoAuthStrategy implements SocialAuthStrategy
{
    /**
     * Get the OAuth redirect URL
     */
    public function getRedirectUrl(): string
    {
        return Socialite::driver('kakao')->stateless()->redirect()->getTargetUrl();
    }

    /**
     * Handle OAuth callback and authenticate user
     */
    public function handleCallback(string $code): array
    {
        $socialUser = Socialite::driver('kakao')->stateless()->user();

        $user = $this->findOrCreateUser($socialUser);

        // Generate token
        $token = $user->createToken(TokenType::AUTH->value)->plainTextToken;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_path' => $user->profile_photo_path,
            ],
            'token' => $token,
        ];
    }

    /**
     * Find or create user from social account data
     */
    public function findOrCreateUser(SocialiteUser $socialUser): User
    {
        return DB::transaction(function () use ($socialUser) {
            // Try to find existing social account
            $socialAccount = SocialAccount::where('provider', SocialProvider::KAKAO)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($socialAccount) {
                // Update token
                $socialAccount->update([
                    'provider_token' => $socialUser->token,
                ]);

                return $socialAccount->user;
            }

            // Try to find user by email (Kakao may not provide email)
            $user = null;
            if ($socialUser->getEmail()) {
                $user = User::where('email', $socialUser->getEmail())->first();
            }

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email' => $socialUser->getEmail() ?? $socialUser->getId() . '@kakao.temp',
                    'password' => Hash::make(Str::random(32)), // Random password
                    'email_verified_at' => now(), // Auto-verify for social accounts
                    'profile_photo_path' => $socialUser->getAvatar(),
                ]);
            }

            // Create social account
            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => SocialProvider::KAKAO,
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token,
            ]);

            return $user;
        });
    }
}
