<?php

namespace App\Console\Commands;

use App\Models\NotificationSetting;
use App\Notifications\WeightReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendWeightRemindersCommand extends Command
{
    protected $signature = 'notifications:weight-reminders';
    protected $description = 'Send weight logging reminder notifications to users';

    public function handle(): int
    {
        $currentTime = Carbon::now()->format('H:i');

        $settings = NotificationSetting::where('weight_reminder_enabled', true)
            ->where('weight_reminder_time', $currentTime)
            ->with('user')
            ->get();

        $count = 0;
        foreach ($settings as $setting) {
            if ($setting->user) {
                $setting->user->notify(new WeightReminderNotification());
                $count++;
            }
        }

        $this->info("Sent {$count} weight reminder(s)");
        return self::SUCCESS;
    }
}
