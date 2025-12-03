<?php

namespace App\Services;

use App\Enums\TokenType;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken(TokenType::AUTH->value)->plainTextToken;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
            'token' => $token,
        ];
    }

    /**
     * Authenticate user and generate token
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new AuthenticationException('이메일 또는 비밀번호가 올바르지 않습니다.');
        }

        // Revoke all existing tokens
        $user->tokens()->delete();

        $token = $user->createToken(TokenType::AUTH->value)->plainTextToken;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_photo_path' => $user->profile_photo_path,
            ],
            'token' => $token,
        ];
    }

    /**
     * Logout user by revoking current token
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Get user information
     *
     * @param User $user
     * @return array
     */
    public function getUserInfo(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_photo_path' => $user->profile_photo_path,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
        ];
    }
}
