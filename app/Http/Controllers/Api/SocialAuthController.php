<?php

namespace App\Http\Controllers\Api;

use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class SocialAuthController extends Controller
{
    public function __construct(
        private SocialAuthService $socialAuthService
    ) {}

    /**
     * Get OAuth redirect URL
     */
    public function redirect(string $provider)
    {
        $providerEnum = $this->validateProvider($provider);

        $redirectUrl = $this->socialAuthService->getRedirectUrl($providerEnum);

        return response()->success([
            'redirect_url' => $redirectUrl,
        ]);
    }

    /**
     * Handle OAuth callback
     */
    public function callback(string $provider, Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $providerEnum = $this->validateProvider($provider);

        $result = $this->socialAuthService->handleCallback(
            $providerEnum,
            $request->code
        );

        return response()->success($result, '소셜 로그인에 성공했습니다.');
    }

    /**
     * Get connected social accounts
     */
    public function connectedAccounts(Request $request)
    {
        $accounts = $this->socialAuthService->getConnectedAccounts($request->user());

        return response()->success([
            'accounts' => $accounts,
        ]);
    }

    /**
     * Disconnect social account
     */
    public function disconnect(string $provider, Request $request)
    {
        $providerEnum = $this->validateProvider($provider);

        $this->socialAuthService->disconnect($request->user(), $providerEnum);

        return response()->success(null, '소셜 계정 연동이 해제되었습니다.');
    }

    /**
     * Validate and convert provider string to enum
     *
     * @param string $provider
     * @return SocialProvider
     */
    protected function validateProvider(string $provider): SocialProvider
    {
        return SocialProvider::from($provider);
    }
}
