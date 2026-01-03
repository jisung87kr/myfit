<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
    ];

    protected $casts = [
        'meal_reminder_enabled' => 'boolean',
        'exercise_reminder_enabled' => 'boolean',
        'weight_reminder_enabled' => 'boolean',
        'daily_summary_enabled' => 'boolean',
        'badge_notification_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'push_enabled' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default settings
     */
    public static function getDefaults(): array
    {
        return [
            'meal_reminder_enabled' => true,
            'breakfast_reminder_time' => '08:00',
            'lunch_reminder_time' => '12:00',
            'dinner_reminder_time' => '18:00',
            'exercise_reminder_enabled' => true,
            'exercise_reminder_time' => '07:00',
            'weight_reminder_enabled' => true,
            'weight_reminder_time' => '07:30',
            'daily_summary_enabled' => true,
            'daily_summary_time' => '21:00',
            'badge_notification_enabled' => true,
            'email_enabled' => true,
            'push_enabled' => true,
        ];
    }
}
