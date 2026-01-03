<?php

namespace App\Console\Commands;

use App\Models\NotificationSetting;
use App\Notifications\MealReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMealRemindersCommand extends Command
{
    protected $signature = 'notifications:meal-reminders {meal_type : breakfast, lunch, or dinner}';
    protected $description = 'Send meal reminder notifications to users';

    public function handle(): int
    {
        $mealType = $this->argument('meal_type');

        if (!in_array($mealType, ['breakfast', 'lunch', 'dinner'])) {
            $this->error("Invalid meal type. Use: breakfast, lunch, or dinner");
            return self::FAILURE;
        }

        $timeColumn = "{$mealType}_reminder_time";
        $currentTime = Carbon::now()->format('H:i');

        $settings = NotificationSetting::where('meal_reminder_enabled', true)
            ->where($timeColumn, $currentTime)
            ->with('user')
            ->get();

        $count = 0;
        foreach ($settings as $setting) {
            if ($setting->user) {
                $setting->user->notify(new MealReminderNotification($mealType));
                $count++;
            }
        }

        $this->info("Sent {$count} {$mealType} reminder(s)");
        return self::SUCCESS;
    }
}
