<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationSettingController extends Controller
{
    /**
     * Get notification settings
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();
        $settings = $user->getNotificationSettings();

        return response()->success([
            'settings' => $settings,
        ], 'Notification settings retrieved successfully');
    }

    /**
     * Update notification settings
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meal_reminder_enabled' => 'boolean',
            'breakfast_reminder_time' => 'date_format:H:i',
            'lunch_reminder_time' => 'date_format:H:i',
            'dinner_reminder_time' => 'date_format:H:i',
            'exercise_reminder_enabled' => 'boolean',
            'exercise_reminder_time' => 'date_format:H:i',
            'weight_reminder_enabled' => 'boolean',
            'weight_reminder_time' => 'date_format:H:i',
            'daily_summary_enabled' => 'boolean',
            'daily_summary_time' => 'date_format:H:i',
            'badge_notification_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $user = auth()->user();
        $settings = $user->getNotificationSettings();
        $settings->update($request->only([
            'meal_reminder_enabled',
            'breakfast_reminder_time',
            'lunch_reminder_time',
            'dinner_reminder_time',
            'exercise_reminder_enabled',
            'exercise_reminder_time',
            'weight_reminder_enabled',
            'weight_reminder_time',
            'daily_summary_enabled',
            'daily_summary_time',
            'badge_notification_enabled',
            'email_enabled',
            'push_enabled',
        ]));

        return response()->success([
            'settings' => $settings->fresh(),
        ], 'Notification settings updated successfully');
    }

    /**
     * Reset to default settings
     */
    public function reset(): JsonResponse
    {
        $user = auth()->user();
        $settings = $user->getNotificationSettings();
        $settings->update(NotificationSetting::getDefaults());

        return response()->success([
            'settings' => $settings->fresh(),
        ], 'Notification settings reset to defaults');
    }

    /**
     * Toggle a specific setting
     */
    public function toggle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'setting' => 'required|string|in:meal_reminder_enabled,exercise_reminder_enabled,weight_reminder_enabled,daily_summary_enabled,badge_notification_enabled,email_enabled,push_enabled',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $user = auth()->user();
        $settings = $user->getNotificationSettings();
        $settingName = $request->setting;
        $settings->update([$settingName => !$settings->$settingName]);

        return response()->success([
            'setting' => $settingName,
            'value' => $settings->fresh()->$settingName,
        ], 'Setting toggled successfully');
    }
}
