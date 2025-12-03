<?php

namespace Tests\Feature;

use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test get OAuth redirect URL for Google
     */
    public function test_get_google_redirect_url(): void
    {
        $response = $this->getJson('/api/auth/google/redirect');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'redirect_url',
                ],
            ]);
    }

    /**
     * Test get OAuth redirect URL for Kakao
     */
    public function test_get_kakao_redirect_url(): void
    {
        $response = $this->getJson('/api/auth/kakao/redirect');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'redirect_url',
                ],
            ]);
    }

    /**
     * Test invalid provider returns error
     */
    public function test_invalid_provider_returns_error(): void
    {
        $response = $this->getJson('/api/auth/invalid/redirect');

        $response->assertStatus(500); // Laravel will throw ValueError for invalid enum
    }

    /**
     * Test Google callback creates new user
     */
    public function test_google_callback_creates_new_user(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-id-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn('test@gmail.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Test User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://avatar.url');
        $socialiteUser->token = 'mock-token';

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($socialiteUser);

        $response = $this->postJson('/api/auth/google/callback', [
            'code' => 'mock-code',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '소셜 로그인에 성공했습니다.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
            'name' => 'Test User',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => SocialProvider::GOOGLE->value,
            'provider_id' => 'google-id-123',
        ]);
    }

    /**
     * Test social callback links to existing user by email
     */
    public function test_social_callback_links_to_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-id-456');
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->token = 'mock-token';

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($socialiteUser);

        $this->postJson('/api/auth/google/callback', [
            'code' => 'mock-code',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => SocialProvider::GOOGLE->value,
            'provider_id' => 'google-id-456',
        ]);

        // Should not create new user
        $this->assertDatabaseCount('users', 1);
    }

    /**
     * Test existing social account returns same user
     */
    public function test_existing_social_account_returns_same_user(): void
    {
        $user = User::factory()->create();
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialProvider::GOOGLE,
            'provider_id' => 'google-id-789',
            'provider_token' => 'old-token',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-id-789');
        $socialiteUser->shouldReceive('getEmail')->andReturn($user->email);
        $socialiteUser->shouldReceive('getName')->andReturn($user->name);
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->token = 'new-token';

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($socialiteUser);

        $response = $this->postJson('/api/auth/google/callback', [
            'code' => 'mock-code',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                ],
            ]);

        // Token should be updated
        $this->assertDatabaseHas('social_accounts', [
            'provider_id' => 'google-id-789',
            'provider_token' => 'new-token',
        ]);
    }

    /**
     * Test callback validation requires code
     */
    public function test_callback_validation_requires_code(): void
    {
        $response = $this->postJson('/api/auth/google/callback', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    /**
     * Test authenticated user can get connected accounts
     */
    public function test_user_can_get_connected_accounts(): void
    {
        $user = User::factory()->create();

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialProvider::GOOGLE,
            'provider_id' => 'google-123',
            'provider_token' => 'token',
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialProvider::KAKAO,
            'provider_id' => 'kakao-456',
            'provider_token' => 'token',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/auth/social/accounts');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.accounts')
            ->assertJsonFragment(['provider' => 'google'])
            ->assertJsonFragment(['provider' => 'kakao']);
    }

    /**
     * Test user can disconnect social account
     */
    public function test_user_can_disconnect_social_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'), // Has password
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialProvider::GOOGLE,
            'provider_id' => 'google-123',
            'provider_token' => 'token',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social/google');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '소셜 계정 연동이 해제되었습니다.',
            ]);

        $this->assertDatabaseMissing('social_accounts', [
            'user_id' => $user->id,
            'provider' => SocialProvider::GOOGLE->value,
        ]);
    }

    /**
     * Test cannot disconnect non-existent social account
     */
    public function test_cannot_disconnect_non_existent_social_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social/google');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['provider']);
    }

    /**
     * Test cannot disconnect last social account without password
     */
    public function test_cannot_disconnect_last_social_account_without_password(): void
    {
        $user = User::factory()->create([
            'password' => null, // Social-only account
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialProvider::GOOGLE,
            'provider_id' => 'google-123',
            'provider_token' => 'token',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social/google');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['provider']);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => SocialProvider::GOOGLE->value,
        ]);
    }

    /**
     * Test can disconnect one social account if another exists (no password)
     */
    public function test_can_disconnect_social_account_if_another_exists(): void
    {
        $user = User::factory()->create([
            'password' => null, // Social-only account
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialProvider::GOOGLE,
            'provider_id' => 'google-123',
            'provider_token' => 'token',
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => SocialProvider::KAKAO,
            'provider_id' => 'kakao-456',
            'provider_token' => 'token',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/auth/social/google');

        $response->assertStatus(200);

        $this->assertDatabaseMissing('social_accounts', [
            'provider' => SocialProvider::GOOGLE->value,
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => SocialProvider::KAKAO->value,
        ]);
    }

    /**
     * Test unauthenticated user cannot access social account endpoints
     */
    public function test_unauthenticated_user_cannot_access_social_endpoints(): void
    {
        $this->getJson('/api/auth/social/accounts')->assertStatus(401);
        $this->deleteJson('/api/auth/social/google')->assertStatus(401);
    }
}
