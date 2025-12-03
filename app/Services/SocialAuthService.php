<?php

namespace App\Services;

use App\Contracts\SocialAuthStrategy;
use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Auth\GoogleAuthStrategy;
use App\Services\Auth\KakaoAuthStrategy;
use Illuminate\Validation\ValidationException;

class SocialAuthService
{
    /**
     * Get OAuth redirect URL for provider
     *
     * @param SocialProvider $provider
     * @return string
     */
    public function getRedirectUrl(SocialProvider $provider): string
    {
        $strategy = $this->getStrategy($provider);

        return $strategy->getRedirectUrl();
    }

    /**
     * Handle OAuth callback
     *
     * @param SocialProvider $provider
     * @param string $code
     * @return array
     */
    public function handleCallback(SocialProvider $provider, string $code): array
    {
        $strategy = $this->getStrategy($provider);

        return $strategy->handleCallback($code);
    }

    /**
     * Disconnect social account
     *
     * @param User $user
     * @param SocialProvider $provider
     * @return void
     * @throws ValidationException
     */
    public function disconnect(User $user, SocialProvider $provider): void
    {
        $socialAccount = SocialAccount::where('user_id', $user->id)
            ->where('provider', $provider)
            ->first();

        if (!$socialAccount) {
            throw ValidationException::withMessages([
                'provider' => ['연동된 소셜 계정을 찾을 수 없습니다.'],
            ]);
        }

        // Check if user has password (not social-only account)
        if (empty($user->password) || $user->password === null) {
            // Check if user has other social accounts
            $otherSocialAccounts = SocialAccount::where('user_id', $user->id)
                ->where('provider', '!=', $provider)
                ->count();

            if ($otherSocialAccounts === 0) {
                throw ValidationException::withMessages([
                    'provider' => ['마지막 소셜 계정은 해제할 수 없습니다. 비밀번호를 먼저 설정해주세요.'],
                ]);
            }
        }

        $socialAccount->delete();
    }

    /**
     * Get list of user's connected social accounts
     *
     * @param User $user
     * @return array
     */
    public function getConnectedAccounts(User $user): array
    {
        $accounts = SocialAccount::where('user_id', $user->id)->get();

        return $accounts->map(function ($account) {
            return [
                'provider' => $account->provider->value,
                'provider_name' => $account->provider->displayName(),
                'connected_at' => $account->created_at->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Get strategy instance for provider
     *
     * @param SocialProvider $provider
     * @return SocialAuthStrategy
     */
    protected function getStrategy(SocialProvider $provider): SocialAuthStrategy
    {
        return match ($provider) {
            SocialProvider::GOOGLE => new GoogleAuthStrategy(),
            SocialProvider::KAKAO => new KakaoAuthStrategy(),
            default => throw new \InvalidArgumentException("Unsupported provider: {$provider->value}"),
        };
    }
}
