<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can get their profile
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '010-1234-5678',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/user/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'phone' => '010-1234-5678',
                ],
            ]);
    }

    /**
     * Test user can update their profile name
     */
    public function test_user_can_update_profile_name(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)
            ->putJson('/api/user/profile', [
                'name' => 'New Name',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '프로필이 수정되었습니다.',
                'data' => [
                    'name' => 'New Name',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Test user can update their phone number
     */
    public function test_user_can_update_phone_number(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/user/profile', [
                'phone' => '010-9876-5432',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '010-9876-5432',
        ]);
    }

    /**
     * Test profile update validation fails with invalid phone
     */
    public function test_profile_update_fails_with_invalid_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/user/profile', [
                'phone' => 'invalid-phone',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /**
     * Test user can change password with correct current password
     */
    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->putJson('/api/user/password', [
                'current_password' => 'OldPassword123',
                'new_password' => 'NewPassword456',
                'new_password_confirmation' => 'NewPassword456',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '비밀번호가 변경되었습니다. 다시 로그인해주세요.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword456', $user->password));
    }

    /**
     * Test password change fails with incorrect current password
     */
    public function test_password_change_fails_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->putJson('/api/user/password', [
                'current_password' => 'WrongPassword',
                'new_password' => 'NewPassword456',
                'new_password_confirmation' => 'NewPassword456',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    /**
     * Test password change fails when new password is same as current
     */
    public function test_password_change_fails_when_new_password_same_as_current(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
        ]);

        $response = $this->actingAs($user)
            ->putJson('/api/user/password', [
                'current_password' => 'Password123',
                'new_password' => 'Password123',
                'new_password_confirmation' => 'Password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /**
     * Test password change fails with weak password
     */
    public function test_password_change_fails_with_weak_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $response = $this->actingAs($user)
            ->putJson('/api/user/password', [
                'current_password' => 'OldPassword123',
                'new_password' => 'weak',
                'new_password_confirmation' => 'weak',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /**
     * Test all tokens are revoked after password change
     */
    public function test_all_tokens_revoked_after_password_change(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        // Create tokens
        $token1 = $user->createToken('token1')->plainTextToken;
        $token2 = $user->createToken('token2')->plainTextToken;

        $this->assertCount(2, $user->tokens);

        $this->actingAs($user)
            ->putJson('/api/user/password', [
                'current_password' => 'OldPassword123',
                'new_password' => 'NewPassword456',
                'new_password_confirmation' => 'NewPassword456',
            ]);

        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    /**
     * Test user can upload profile photo
     */
    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('profile.jpg', 500, 500);

        $response = $this->actingAs($user)
            ->postJson('/api/user/profile/photo', [
                'photo' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '프로필 사진이 업로드되었습니다.',
            ]);

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    /**
     * Test photo upload fails with invalid file
     */
    public function test_photo_upload_fails_with_invalid_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf');

        $response = $this->actingAs($user)
            ->postJson('/api/user/profile/photo', [
                'photo' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    /**
     * Test photo upload fails with file too large
     */
    public function test_photo_upload_fails_with_large_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB

        $response = $this->actingAs($user)
            ->postJson('/api/user/profile/photo', [
                'photo' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    /**
     * Test old photo is deleted when uploading new one
     */
    public function test_old_photo_deleted_when_uploading_new_one(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // Upload first photo
        $firstPhoto = UploadedFile::fake()->image('first.jpg');
        $this->actingAs($user)
            ->postJson('/api/user/profile/photo', ['photo' => $firstPhoto]);

        $user->refresh();
        $firstPhotoPath = $user->profile_photo_path;
        Storage::disk('public')->assertExists($firstPhotoPath);

        // Upload second photo
        $secondPhoto = UploadedFile::fake()->image('second.jpg');
        $this->actingAs($user)
            ->postJson('/api/user/profile/photo', ['photo' => $secondPhoto]);

        $user->refresh();

        // First photo should be deleted
        Storage::disk('public')->assertMissing($firstPhotoPath);
        // Second photo should exist
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    /**
     * Test user can delete profile photo
     */
    public function test_user_can_delete_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // Upload photo first
        $photo = UploadedFile::fake()->image('profile.jpg');
        $this->actingAs($user)
            ->postJson('/api/user/profile/photo', ['photo' => $photo]);

        $user->refresh();
        $photoPath = $user->profile_photo_path;

        // Delete photo
        $response = $this->actingAs($user)
            ->deleteJson('/api/user/profile/photo');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '프로필 사진이 삭제되었습니다.',
            ]);

        $user->refresh();
        $this->assertNull($user->profile_photo_path);
        Storage::disk('public')->assertMissing($photoPath);
    }

    /**
     * Test unauthenticated user cannot access profile endpoints
     */
    public function test_unauthenticated_user_cannot_access_profile_endpoints(): void
    {
        $this->getJson('/api/user/profile')->assertStatus(401);
        $this->putJson('/api/user/profile')->assertStatus(401);
        $this->putJson('/api/user/password')->assertStatus(401);
        $this->postJson('/api/user/profile/photo')->assertStatus(401);
        $this->deleteJson('/api/user/profile/photo')->assertStatus(401);
    }
}
