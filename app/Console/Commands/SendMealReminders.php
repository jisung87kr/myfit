<?php

namespace App\Console\Commands;

use App\Models\NotificationSetting;
use App\Models\User;
use App\Notifications\MealReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendMealReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:meal-reminders {meal_type : breakfast, lunch, or dinner}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send meal reminder notifications to users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mealType = $this->argument('meal_type');

        if (!in_array($mealType, ['breakfast', 'lunch', 'dinner'])) {
            $this->error('Invalid meal type. Use breakfast, lunch, or dinner.');
            return self::FAILURE;
        }

        $currentTime = Carbon::now()->format('H:i');
        $timeColumn = "{$mealType}_reminder_time";

        // Find users who should receive this reminder
        $settings = NotificationSetting::where('meal_reminder_enabled', true)
            ->where($timeColumn, $currentTime)
            ->with('user')
            ->get();

        $count = 0;

        foreach ($settings as $setting) {
            if (!$setting->user) {
                continue;
            }

            // Check if user is not disabled
            if ($setting->user->disabled_at) {
                continue;
            }

            $setting->user->notify(new MealReminderNotification($mealType));
            $count++;
        }

        $this->info("Sent {$count} {$mealType} reminder(s) at {$currentTime}");

        return self::SUCCESS;
    }
}
