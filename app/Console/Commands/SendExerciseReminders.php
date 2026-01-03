<?php

namespace App\Console\Commands;

use App\Models\NotificationSetting;
use App\Notifications\ExerciseReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendExerciseReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:exercise-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send exercise reminder notifications to users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $currentTime = Carbon::now()->format('H:i');

        // Find users who should receive this reminder
        $settings = NotificationSetting::where('exercise_reminder_enabled', true)
            ->where('exercise_reminder_time', $currentTime)
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

            $setting->user->notify(new ExerciseReminderNotification());
            $count++;
        }

        $this->info("Sent {$count} exercise reminder(s) at {$currentTime}");

        return self::SUCCESS;
    }
}
