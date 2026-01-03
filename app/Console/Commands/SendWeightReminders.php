<?php

namespace App\Console\Commands;

use App\Models\NotificationSetting;
use App\Models\WeightLog;
use App\Notifications\WeightReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendWeightReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:weight-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weight logging reminder notifications to users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $currentTime = Carbon::now()->format('H:i');
        $today = Carbon::now()->format('Y-m-d');

        // Find users who should receive this reminder
        $settings = NotificationSetting::where('weight_reminder_enabled', true)
            ->where('weight_reminder_time', $currentTime)
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

            // Check if user already logged weight today
            $hasLoggedToday = WeightLog::where('user_id', $setting->user->id)
                ->whereDate('date', $today)
                ->exists();

            if ($hasLoggedToday) {
                continue;
            }

            $setting->user->notify(new WeightReminderNotification());
            $count++;
        }

        $this->info("Sent {$count} weight reminder(s) at {$currentTime}");

        return self::SUCCESS;
    }
}
