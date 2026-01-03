<?php

namespace App\Console\Commands;

use App\Models\ExerciseLog;
use App\Models\MealLog;
use App\Models\NotificationSetting;
use App\Notifications\DailySummaryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily summary notifications to users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $currentTime = Carbon::now()->format('H:i');
        $today = Carbon::now()->format('Y-m-d');

        // Find users who should receive this reminder
        $settings = NotificationSetting::where('daily_summary_enabled', true)
            ->where('daily_summary_time', $currentTime)
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

            // Generate summary for today
            $summary = $this->generateDailySummary($setting->user->id, $today);

            // Only send if there's some activity
            if ($summary['meal_count'] == 0 && $summary['exercise_count'] == 0) {
                continue;
            }

            $setting->user->notify(new DailySummaryNotification($summary));
            $count++;
        }

        $this->info("Sent {$count} daily summary notification(s) at {$currentTime}");

        return self::SUCCESS;
    }

    /**
     * Generate daily summary for a user
     */
    private function generateDailySummary(int $userId, string $date): array
    {
        $mealSummary = MealLog::getDailySummary($userId, $date);
        $exerciseSummary = ExerciseLog::getDailySummary($userId, $date);

        return [
            'date' => $date,
            'calories_consumed' => $mealSummary['total_calories'],
            'calories_burned' => $exerciseSummary['total_calories_burned'],
            'net_calories' => $mealSummary['total_calories'] - $exerciseSummary['total_calories_burned'],
            'meal_count' => $mealSummary['meal_count'],
            'exercise_count' => $exerciseSummary['exercise_count'],
            'protein_g' => $mealSummary['total_protein_g'],
            'carbs_g' => $mealSummary['total_carbs_g'],
            'fat_g' => $mealSummary['total_fat_g'],
            'exercise_duration_minutes' => $exerciseSummary['total_duration_minutes'],
        ];
    }
}
