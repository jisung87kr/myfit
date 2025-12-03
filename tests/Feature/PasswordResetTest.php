<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can request password reset link
     */
    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/password/email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '비밀번호 재설정 링크가 이메일로 전송되었습니다.',
            ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    /**
     * Test password reset fails with non-existent email
     */
    public function test_password_reset_fails_with_non_existent_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/password/email', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        Notification::assertNothingSent();
    }

    /**
     * Test password reset email validation
     */
    public function test_password_reset_email_validation(): void
    {
        // Missing email
        $response = $this->postJson('/api/password/email', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Invalid email format
        $response = $this->postJson('/api/password/email', [
            'email' => 'invalid-email',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user can reset password with valid token
     */
    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123'),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '비밀번호가 성공적으로 변경되었습니다. 새 비밀번호로 로그인해주세요.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword456', $user->password));
    }

    /**
     * Test password reset fails with invalid token
     */
    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123'),
        ]);

        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => 'invalid-token',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $user->password));
    }

    /**
     * Test password reset fails with expired token
     */
    public function test_password_reset_fails_with_expired_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Password::createToken($user);

        // Simulate token expiration by traveling forward in time
        $this->travel(61)->minutes();

        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test password reset validation
     */
    public function test_password_reset_validation(): void
    {
        // Missing fields
        $response = $this->postJson('/api/password/reset', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'token', 'password']);

        // Weak password
        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => 'some-token',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Password mismatch
        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => 'some-token',
            'password' => 'NewPassword123',
            'password_confirmation' => 'DifferentPassword456',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test all tokens are revoked after password reset
     */
    public function test_all_tokens_revoked_after_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123'),
        ]);

        // Create tokens
        $user->createToken('token1');
        $user->createToken('token2');
        $this->assertCount(2, $user->tokens);

        $token = Password::createToken($user);

        $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    /**
     * Test remember token is reset after password reset
     */
    public function test_remember_token_reset_after_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123'),
        ]);

        $oldRememberToken = $user->remember_token;
        $token = Password::createToken($user);

        $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $user->refresh();
        $this->assertNotEquals($oldRememberToken, $user->remember_token);
    }

    /**
     * Test notification contains correct data
     */
    public function test_notification_contains_correct_data(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->postJson('/api/password/email', [
            'email' => 'test@example.com',
        ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification, $channels) use ($user) {
            $mailData = $notification->toMail($user);

            // Check mail subject
            $this->assertEquals('비밀번호 재설정 요청', $mailData->subject);

            // Check greeting contains user name
            $this->assertStringContainsString($user->name, $mailData->greeting);

            return true;
        });
    }

    /**
     * Test user can request multiple reset links
     */
    public function test_user_can_request_multiple_reset_links(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // First request
        $this->postJson('/api/password/email', ['email' => 'test@example.com'])
            ->assertStatus(200);

        // Second request
        $this->postJson('/api/password/email', ['email' => 'test@example.com'])
            ->assertStatus(200);

        Notification::assertSentTo($user, ResetPasswordNotification::class, 2);
    }

    /**
     * Test old token becomes invalid after new token is created
     */
    public function test_old_token_invalid_after_new_token_created(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123'),
        ]);

        $oldToken = Password::createToken($user);
        $newToken = Password::createToken($user);

        // Try with old token
        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => $oldToken,
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Verify password wasn't changed
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $user->password));

        // New token should work
        $response = $this->postJson('/api/password/reset', [
            'email' => 'test@example.com',
            'token' => $newToken,
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword456', $user->password));
    }
}
