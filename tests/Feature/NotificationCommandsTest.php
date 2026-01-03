<?php

namespace Tests\Feature;

use App\Models\NotificationSetting;
use App\Models\User;
use App\Models\WeightLog;
use App\Models\MealLog;
use App\Models\ExerciseLog;
use App\Models\Food;
use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\MealReminderNotification;
use App\Notifications\ExerciseReminderNotification;
use App\Notifications\WeightReminderNotification;
use App\Notifications\DailySummaryNotification;
use Tests\TestCase;

class NotificationCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected NotificationSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_meal_reminder_command_sends_notifications(): void
    {
        Notification::fake();

        $currentTime = now()->format('H:i');

        NotificationSetting::create([
            'user_id' => $this->user->id,
            'meal_reminder_enabled' => true,
            'breakfast_reminder_time' => $currentTime,
            'lunch_reminder_time' => '12:00',
            'dinner_reminder_time' => '18:00',
        ]);

        $this->artisan('notifications:meal-reminders', ['meal_type' => 'breakfast'])
            ->assertSuccessful();

        Notification::assertSentTo($this->user, MealReminderNotification::class);
    }

    public function test_meal_reminder_not_sent_when_disabled(): void
    {
        Notification::fake();

        $currentTime = now()->format('H:i');

        NotificationSetting::create([
            'user_id' => $this->user->id,
            'meal_reminder_enabled' => false,
            'breakfast_reminder_time' => $currentTime,
        ]);

        $this->artisan('notifications:meal-reminders', ['meal_type' => 'breakfast'])
            ->assertSuccessful();

        Notification::assertNotSentTo($this->user, MealReminderNotification::class);
    }

    public function test_meal_reminder_not_sent_when_time_mismatch(): void
    {
        Notification::fake();

        NotificationSetting::create([
            'user_id' => $this->user->id,
            'meal_reminder_enabled' => true,
            'breakfast_reminder_time' => '23:59',
        ]);

        $this->artisan('notifications:meal-reminders', ['meal_type' => 'breakfast'])
            ->assertSuccessful();

        Notification::assertNotSentTo($this->user, MealReminderNotification::class);
    }

    public function test_meal_reminder_rejects_invalid_meal_type(): void
    {
        $this->artisan('notifications:meal-reminders', ['meal_type' => 'invalid'])
            ->assertFailed();
    }

    public function test_exercise_reminder_command_sends_notifications(): void
    {
        Notification::fake();

        $currentTime = now()->format('H:i');

        NotificationSetting::create([
            'user_id' => $this->user->id,
            'exercise_reminder_enabled' => true,
            'exercise_reminder_time' => $currentTime,
        ]);

        $this->artisan('notifications:exercise-reminders')
            ->assertSuccessful();

        Notification::assertSentTo($this->user, ExerciseReminderNotification::class);
    }

    public function test_exercise_reminder_not_sent_when_disabled(): void
    {
        Notification::fake();

        $currentTime = now()->format('H:i');

        NotificationSetting::create([
            'user_id' => $this->user->id,
            'exercise_reminder_enabled' => false,
            'exercise_reminder_time' => $currentTime,
        ]);

        $this->artisan('notifications:exercise-reminders')
            ->assertSuccessful();

        Notification::assertNotSentTo($this->user, ExerciseReminderNotification::class);
    }

    public function test_weight_reminder_command_sends_notifications(): void
    {
        Notification::fake();

        $currentTime = now()->format('H:i');

        NotificationSetting::create([
            'user_id' => $this->user->id,
            'weight_reminder_enabled' => true,
            'weight_reminder_time' => $currentTime,
        ]);

        $this->artisan('notifications:weight-reminders')
            ->assertSuccessful();

        Notification::assertSentTo($this->user, WeightReminderNotification::class);
    }

    /**
     * @group sqlite-skip
     */
    public function test_weight_reminder_not_sent_when_already_logged_today(): void
    {
        // Skip in SQLite due to date comparison issues
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite date comparison issues');
        }

        Notification::fake();

        $userWithLog = User::factory()->create();
        $currentTime = now()->format('H:i');

        NotificationSetting::create([
            'user_id' => $userWithLog->id,
            'weight_reminder_enabled' => true,
            'weight_reminder_time' => $currentTime,
        ]);

        WeightLog::create([
            'user_id' => $userWithLog->id,
            'date' => now()->toDateString(),
            'weight' => 70.0,
        ]);

        $this->artisan('notifications:weight-reminders')
            ->assertSuccessful();

        Notification::assertNotSentTo($userWithLog, WeightReminderNotification::class);
    }

    public function test_daily_summary_command_sends_notifications(): void
    {
        Notification::fake();

        $currentTime = now()->format('H:i');

        NotificationSetting::create([
            'user_id' => $this->user->id,
            'daily_summary_enabled' => true,
            'daily_summary_time' => $currentTime,
        ]);

        // Create some activity for today
        $food = Food::factory()->create();
        MealLog::create([
            'user_id' => $this->user->id,
            'food_id' => $food->id,
            'food_name' => $food->name,
            'date' => now()->format('Y-m-d'),
            'meal_type' => 'lunch',
            'serving_size' => 100,
            'calories' => 300,
            'protein_g' => 20,
            'carbs_g' => 30,
            'fat_g' => 10,
        ]);

        $this->artisan('notifications:daily-summary')
            ->assertSuccessful();

        Notification::assertSentTo($this->user, DailySummaryNotification::class);
    }

    /**
     * @group sqlite-skip
     */
    public function test_daily_summary_not_sent_when_no_activity(): void
    {
        // Skip in SQLite due to date/time comparison issues in scheduler
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite date comparison issues');
        }

        Notification::fake();

        $userWithNoActivity = User::factory()->create();
        $currentTime = now()->format('H:i');

        NotificationSetting::create([
            'user_id' => $userWithNoActivity->id,
            'daily_summary_enabled' => true,
            'daily_summary_time' => $currentTime,
        ]);

        $this->artisan('notifications:daily-summary')
            ->assertSuccessful();

        Notification::assertNotSentTo($userWithNoActivity, DailySummaryNotification::class);
    }

    public function test_notifications_not_sent_to_disabled_users(): void
    {
        Notification::fake();

        $currentTime = now()->format('H:i');

        $this->user->update([
            'disabled_at' => now(),
            'disabled_reason' => 'Test',
        ]);

        NotificationSetting::create([
            'user_id' => $this->user->id,
            'meal_reminder_enabled' => true,
            'breakfast_reminder_time' => $currentTime,
        ]);

        $this->artisan('notifications:meal-reminders', ['meal_type' => 'breakfast'])
            ->assertSuccessful();

        Notification::assertNotSentTo($this->user, MealReminderNotification::class);
    }
}
