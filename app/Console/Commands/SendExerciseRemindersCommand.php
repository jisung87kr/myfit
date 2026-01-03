<?php

namespace App\Console\Commands;

use App\Models\NotificationSetting;
use App\Notifications\ExerciseReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendExerciseRemindersCommand extends Command
{
    protected $signature = 'notifications:exercise-reminders';
    protected $description = 'Send exercise reminder notifications to users';

    public function handle(): int
    {
        $currentTime = Carbon::now()->format('H:i');

        $settings = NotificationSetting::where('exercise_reminder_enabled', true)
            ->where('exercise_reminder_time', $currentTime)
            ->with('user')
            ->get();

        $count = 0;
        foreach ($settings as $setting) {
            if ($setting->user) {
                $setting->user->notify(new ExerciseReminderNotification());
                $count++;
            }
        }

        $this->info("Sent {$count} exercise reminder(s)");
        return self::SUCCESS;
    }
}
