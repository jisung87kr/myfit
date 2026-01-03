<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            // Streak badges - Meal
            [
                'code' => 'meal_streak_7',
                'name' => '일주일 식단 기록',
                'description' => '7일 연속으로 식단을 기록했습니다!',
                'icon' => 'streak-bronze',
                'category' => 'streak',
                'type' => 'bronze',
                'requirement_value' => 7,
                'requirement_type' => 'days',
            ],
            [
                'code' => 'meal_streak_30',
                'name' => '한 달 식단 마스터',
                'description' => '30일 연속으로 식단을 기록했습니다!',
                'icon' => 'streak-silver',
                'category' => 'streak',
                'type' => 'silver',
                'requirement_value' => 30,
                'requirement_type' => 'days',
            ],
            [
                'code' => 'meal_streak_100',
                'name' => '100일 식단 챔피언',
                'description' => '100일 연속으로 식단을 기록했습니다!',
                'icon' => 'streak-gold',
                'category' => 'streak',
                'type' => 'gold',
                'requirement_value' => 100,
                'requirement_type' => 'days',
            ],

            // Streak badges - Exercise
            [
                'code' => 'exercise_streak_7',
                'name' => '일주일 운동 도전',
                'description' => '7일 연속으로 운동을 기록했습니다!',
                'icon' => 'exercise-bronze',
                'category' => 'streak',
                'type' => 'bronze',
                'requirement_value' => 7,
                'requirement_type' => 'days',
            ],
            [
                'code' => 'exercise_streak_30',
                'name' => '한 달 운동 마스터',
                'description' => '30일 연속으로 운동을 기록했습니다!',
                'icon' => 'exercise-silver',
                'category' => 'streak',
                'type' => 'silver',
                'requirement_value' => 30,
                'requirement_type' => 'days',
            ],

            // Meal badges
            [
                'code' => 'meal_count_10',
                'name' => '첫 10끼',
                'description' => '10개의 식사를 기록했습니다!',
                'icon' => 'meal-bronze',
                'category' => 'meal',
                'type' => 'bronze',
                'requirement_value' => 10,
                'requirement_type' => 'count',
            ],
            [
                'code' => 'meal_count_100',
                'name' => '100끼 기록',
                'description' => '100개의 식사를 기록했습니다!',
                'icon' => 'meal-silver',
                'category' => 'meal',
                'type' => 'silver',
                'requirement_value' => 100,
                'requirement_type' => 'count',
            ],
            [
                'code' => 'meal_count_500',
                'name' => '500끼 달성',
                'description' => '500개의 식사를 기록했습니다!',
                'icon' => 'meal-gold',
                'category' => 'meal',
                'type' => 'gold',
                'requirement_value' => 500,
                'requirement_type' => 'count',
            ],

            // Exercise badges
            [
                'code' => 'exercise_count_10',
                'name' => '첫 10회 운동',
                'description' => '10회의 운동을 기록했습니다!',
                'icon' => 'exercise-bronze',
                'category' => 'exercise',
                'type' => 'bronze',
                'requirement_value' => 10,
                'requirement_type' => 'count',
            ],
            [
                'code' => 'exercise_count_50',
                'name' => '50회 운동 달성',
                'description' => '50회의 운동을 기록했습니다!',
                'icon' => 'exercise-silver',
                'category' => 'exercise',
                'type' => 'silver',
                'requirement_value' => 50,
                'requirement_type' => 'count',
            ],
            [
                'code' => 'exercise_calories_10000',
                'name' => '1만 칼로리 소모',
                'description' => '총 10,000kcal를 운동으로 소모했습니다!',
                'icon' => 'calorie-gold',
                'category' => 'exercise',
                'type' => 'gold',
                'requirement_value' => 10000,
                'requirement_type' => 'calories',
            ],

            // Weight badges
            [
                'code' => 'weight_count_10',
                'name' => '꾸준한 체중 관리',
                'description' => '10회의 체중을 기록했습니다!',
                'icon' => 'weight-bronze',
                'category' => 'weight',
                'type' => 'bronze',
                'requirement_value' => 10,
                'requirement_type' => 'count',
            ],
            [
                'code' => 'weight_count_30',
                'name' => '한 달 체중 기록',
                'description' => '30회의 체중을 기록했습니다!',
                'icon' => 'weight-silver',
                'category' => 'weight',
                'type' => 'silver',
                'requirement_value' => 30,
                'requirement_type' => 'count',
            ],
            [
                'code' => 'weight_loss_5',
                'name' => '5kg 감량 성공',
                'description' => '첫 기록 대비 5kg 감량에 성공했습니다!',
                'icon' => 'loss-gold',
                'category' => 'weight',
                'type' => 'gold',
                'requirement_value' => 5,
                'requirement_type' => 'kg',
            ],

            // Milestone badges
            [
                'code' => 'total_logs_100',
                'name' => '100번째 기록',
                'description' => '총 100개의 기록을 달성했습니다!',
                'icon' => 'milestone-bronze',
                'category' => 'milestone',
                'type' => 'bronze',
                'requirement_value' => 100,
                'requirement_type' => 'count',
            ],
            [
                'code' => 'total_logs_500',
                'name' => '500번째 기록',
                'description' => '총 500개의 기록을 달성했습니다!',
                'icon' => 'milestone-silver',
                'category' => 'milestone',
                'type' => 'silver',
                'requirement_value' => 500,
                'requirement_type' => 'count',
            ],
            [
                'code' => 'total_logs_1000',
                'name' => '1000번째 기록',
                'description' => '총 1000개의 기록을 달성했습니다! 대단합니다!',
                'icon' => 'milestone-gold',
                'category' => 'milestone',
                'type' => 'gold',
                'requirement_value' => 1000,
                'requirement_type' => 'count',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['code' => $badge['code']],
                $badge
            );
        }
    }
}
