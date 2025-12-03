<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    /**
     * Get user profile
     *
     * @param User $user
     * @return array
     */
    public function getProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_photo_path' => $user->profile_photo_path
                ? Storage::url($user->profile_photo_path)
                : null,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at->toIso8601String(),
        ];
    }

    /**
     * Update user profile
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    public function updateProfile(User $user, array $data): array
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }

        $user->update($updateData);

        return $this->getProfile($user->fresh());
    }

    /**
     * Change user password
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return void
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['현재 비밀번호가 올바르지 않습니다.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();
    }

    /**
     * Upload profile photo
     *
     * @param User $user
     * @param UploadedFile $photo
     * @return array
     */
    public function uploadPhoto(User $user, UploadedFile $photo): array
    {
        // Delete old photo if exists
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Store new photo
        $path = $photo->store('profile-photos', 'public');

        $user->update([
            'profile_photo_path' => $path,
        ]);

        return $this->getProfile($user->fresh());
    }

    /**
     * Delete profile photo
     *
     * @param User $user
     * @return void
     */
    public function deletePhoto(User $user): void
    {
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);

            $user->update([
                'profile_photo_path' => null,
            ]);
        }
    }
}
