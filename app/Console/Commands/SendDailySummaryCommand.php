<?php

namespace App\Console\Commands;

use App\Models\ExerciseLog;
use App\Models\MealLog;
use App\Models\NotificationSetting;
use App\Notifications\DailySummaryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailySummaryCommand extends Command
{
    protected $signature = 'notifications:daily-summary';
    protected $description = 'Send daily summary notifications to users';

    public function handle(): int
    {
        $currentTime = Carbon::now()->format('H:i');
        $today = Carbon::today();

        $settings = NotificationSetting::where('daily_summary_enabled', true)
            ->where('daily_summary_time', $currentTime)
            ->with('user')
            ->get();

        $count = 0;
        foreach ($settings as $setting) {
            if (!$setting->user) {
                continue;
            }

            $userId = $setting->user->id;

            // Calculate today's summary
            $mealStats = MealLog::where('user_id', $userId)
                ->whereDate('logged_at', $today)
                ->selectRaw('COUNT(*) as meal_count, COALESCE(SUM(calories), 0) as calories_consumed')
                ->first();

            $exerciseStats = ExerciseLog::where('user_id', $userId)
                ->whereDate('logged_at', $today)
                ->selectRaw('COUNT(*) as exercise_count, COALESCE(SUM(calories_burned), 0) as calories_burned')
                ->first();

            $summary = [
                'date' => $today->format('Y-m-d'),
                'calories_consumed' => (int) $mealStats->calories_consumed,
                'calories_burned' => (int) $exerciseStats->calories_burned,
                'meal_count' => (int) $mealStats->meal_count,
                'exercise_count' => (int) $exerciseStats->exercise_count,
            ];

            $setting->user->notify(new DailySummaryNotification($summary));
            $count++;
        }

        $this->info("Sent {$count} daily summary notification(s)");
        return self::SUCCESS;
    }
}
