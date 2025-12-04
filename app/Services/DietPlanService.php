<?php

namespace App\Services;

use App\Models\DailyExercisePlan;
use App\Models\DailyMealPlan;
use App\Models\DietPlan;
use App\Models\Food;
use App\Models\Exercise;
use App\Models\MealPlanItem;
use App\Models\User;
use App\Models\UserCalculation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DietPlanService
{
    private const PLAN_DURATION_DAYS = 7;
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';

    /**
     * Generate a new diet plan for a user
     */
    public function generatePlan(User $user, ?int $surveyResponseId = null): DietPlan
    {
        // Create initial plan with 'generating' status
        $dietPlan = DietPlan::create([
            'user_id' => $user->id,
            'survey_response_id' => $surveyResponseId,
            'status' => 'generating',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(self::PLAN_DURATION_DAYS - 1),
            'target_calories_per_day' => $this->getTargetCalories($user),
        ]);

        return $dietPlan;
    }

    /**
     * Generate plan using GPT API
     */
    public function generateWithAI(DietPlan $dietPlan): void
    {
        try {
            $user = $dietPlan->user;

            // Gather user data
            $userData = $this->getUserData($user);

            // Build prompt
            $prompt = $this->buildPrompt($userData, $dietPlan);

            // Save the prompt
            $dietPlan->update(['generation_prompt' => $prompt]);

            // Call GPT API
            $response = $this->callGPTAPI($prompt);

            // Parse and save the plan
            $this->savePlanFromAIResponse($dietPlan, $response);

            // Mark as active
            $dietPlan->markAsActive();

        } catch (\Exception $e) {
            Log::error('Failed to generate diet plan', [
                'diet_plan_id' => $dietPlan->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get user data from survey and calculations
     */
    private function getUserData(User $user): array
    {
        // Get latest calculation
        $calculation = UserCalculation::where('user_id', $user->id)
            ->latest('calculated_at')
            ->first();

        // Get survey responses
        $surveyResponse = $user->surveyResponses()->latest()->first();

        $responses = [];
        if ($surveyResponse) {
            foreach ($surveyResponse->answers as $answer) {
                $responses[$answer->question->question_text] = $answer->answer_value;
            }
        }

        return [
            'gender' => $responses['성별'] ?? '알 수 없음',
            'age' => $responses['나이'] ?? 30,
            'current_weight' => $responses['현재 체중 (kg)'] ?? 70,
            'target_weight' => $responses['목표 체중 (kg)'] ?? 65,
            'height' => $responses['키 (cm)'] ?? 170,
            'goal' => $responses['주요 목표'] ?? '체중 감량',
            'goal_period' => $responses['희망 감량 기간'] ?? '8주',
            'activity_level' => $responses['일일 활동량'] ?? '보통',
            'exercise_experience' => $responses['운동 경험'] ?? '초보',
            'meals_per_day' => $responses['하루 식사 횟수'] ?? '3회',
            'preferred_foods' => $responses['선호하는 음식 종류'] ?? '한식',
            'disliked_foods' => $responses['싫어하는 음식 재료'] ?? [],
            'dietary_restrictions' => $responses['식이 제한'] ?? '없음',
            'can_cook' => $responses['조리 가능 여부'] ?? '직접 조리',
            'target_calories' => $calculation?->target_calories ?? 1500,
            'target_protein_g' => $calculation?->target_protein_g ?? 112,
            'target_carbs_g' => $calculation?->target_carbs_g ?? 150,
            'target_fat_g' => $calculation?->target_fat_g ?? 50,
        ];
    }

    /**
     * Build GPT prompt
     */
    private function buildPrompt(array $userData, DietPlan $dietPlan): string
    {
        return <<<PROMPT
당신은 영양학과 운동 전문가입니다. 다음 사용자 정보를 바탕으로 7일간의 맞춤형 다이어트 플랜을 작성해주세요.

[사용자 정보]
- 성별: {$userData['gender']}
- 나이: {$userData['age']}세
- 현재 체중: {$userData['current_weight']}kg
- 목표 체중: {$userData['target_weight']}kg
- 키: {$userData['height']}cm
- 주요 목표: {$userData['goal']}
- 목표 기간: {$userData['goal_period']}
- 일일 목표 칼로리: {$userData['target_calories']}kcal
- 활동량: {$userData['activity_level']}
- 운동 경험: {$userData['exercise_experience']}
- 하루 식사 횟수: {$userData['meals_per_day']}
- 선호 음식: {$userData['preferred_foods']}
- 싫어하는 재료: {$userData['disliked_foods']}
- 식이 제한: {$userData['dietary_restrictions']}
- 조리 가능 여부: {$userData['can_cook']}

[영양소 목표]
- 탄수화물: {$userData['target_carbs_g']}g
- 단백질: {$userData['target_protein_g']}g
- 지방: {$userData['target_fat_g']}g

[플랜 요구사항]
1. 7일간의 일별 식단 (아침, 점심, 저녁, 간식)
2. 각 끼니별 음식명, 1인분량(g), 칼로리 및 영양소 (단백질, 탄수화물, 지방)
3. 주 3-5회 운동 스케줄 (운동명, 시간, 강도, 예상 소모 칼로리)
4. 실천 가능한 팁
5. 사용자의 선호도와 제한사항을 반영한 현실적인 플랜

[응답 형식: 반드시 아래 JSON 형식으로만 응답하세요]
{
  "summary": "플랜 요약 및 근거 (200자 이내)",
  "daily_plans": [
    {
      "day": 1,
      "date": "{$dietPlan->start_date->format('Y-m-d')}",
      "meals": {
        "breakfast": [
          {
            "food_name": "백미밥",
            "serving_size": 210,
            "calories": 300,
            "protein_g": 5.4,
            "carbs_g": 65.8,
            "fat_g": 0.6
          }
        ],
        "lunch": [...],
        "dinner": [...],
        "snack": [...]
      },
      "exercise": {
        "exercise_name": "빠르게 걷기",
        "duration_minutes": 30,
        "intensity": "보통",
        "calories_burned": 150
      },
      "tips": "하루 실천 팁"
    }
    // ... 2일차부터 7일차까지
  ]
}

중요:
- 반드시 위 JSON 형식만 응답하세요
- 각 음식의 영양소 정보는 정확해야 합니다
- 일일 총 칼로리는 목표 칼로리의 ±10% 범위 내로 유지하세요
- 운동은 주 3-5회만 포함하세요 (나머지 날은 null)
PROMPT;
    }

    /**
     * Call OpenAI GPT API
     */
    private function callGPTAPI(string $prompt): array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post(self::OPENAI_API_URL, [
            'model' => 'gpt-4-turbo-preview',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => '당신은 영양학과 운동 전문가입니다. 항상 JSON 형식으로만 응답합니다.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ]);

        if (!$response->successful()) {
            throw new \Exception('GPT API call failed: ' . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            throw new \Exception('No content in GPT response');
        }

        return json_decode($content, true);
    }

    /**
     * Save plan from AI response
     */
    private function savePlanFromAIResponse(DietPlan $dietPlan, array $response): void
    {
        DB::transaction(function () use ($dietPlan, $response) {
            // Save summary
            $dietPlan->update([
                'ai_summary' => $response['summary'] ?? '맞춤형 다이어트 플랜이 생성되었습니다.',
            ]);

            // Save daily plans
            foreach ($response['daily_plans'] as $dayData) {
                $dayNumber = $dayData['day'];
                $date = Carbon::parse($dayData['date']);

                // Calculate totals from meals
                $totalCalories = 0;
                $totalProtein = 0;
                $totalCarbs = 0;
                $totalFat = 0;

                foreach (['breakfast', 'lunch', 'dinner', 'snack'] as $mealType) {
                    if (isset($dayData['meals'][$mealType])) {
                        foreach ($dayData['meals'][$mealType] as $food) {
                            $totalCalories += $food['calories'];
                            $totalProtein += $food['protein_g'];
                            $totalCarbs += $food['carbs_g'];
                            $totalFat += $food['fat_g'];
                        }
                    }
                }

                // Create daily meal plan
                $dailyMealPlan = DailyMealPlan::create([
                    'diet_plan_id' => $dietPlan->id,
                    'day_number' => $dayNumber,
                    'date' => $date,
                    'total_calories' => $totalCalories,
                    'total_protein_g' => $totalProtein,
                    'total_carbs_g' => $totalCarbs,
                    'total_fat_g' => $totalFat,
                    'tips' => $dayData['tips'] ?? null,
                ]);

                // Save meal items
                foreach (['breakfast', 'lunch', 'dinner', 'snack'] as $mealType) {
                    if (isset($dayData['meals'][$mealType])) {
                        $order = 0;
                        foreach ($dayData['meals'][$mealType] as $foodData) {
                            // Try to find matching food in database
                            $food = Food::where('name', $foodData['food_name'])->first();

                            MealPlanItem::create([
                                'daily_meal_plan_id' => $dailyMealPlan->id,
                                'meal_type' => $mealType,
                                'food_id' => $food?->id,
                                'food_name' => $foodData['food_name'],
                                'serving_size' => $foodData['serving_size'],
                                'calories' => $foodData['calories'],
                                'protein_g' => $foodData['protein_g'],
                                'carbs_g' => $foodData['carbs_g'],
                                'fat_g' => $foodData['fat_g'],
                                'order' => $order++,
                            ]);
                        }
                    }
                }

                // Save exercise plan (if exists)
                if (isset($dayData['exercise']) && $dayData['exercise']) {
                    $exerciseData = $dayData['exercise'];

                    // Try to find matching exercise
                    $exercise = Exercise::where('name', $exerciseData['exercise_name'])->first();

                    DailyExercisePlan::create([
                        'diet_plan_id' => $dietPlan->id,
                        'day_number' => $dayNumber,
                        'date' => $date,
                        'exercise_id' => $exercise?->id,
                        'exercise_name' => $exerciseData['exercise_name'],
                        'duration_minutes' => $exerciseData['duration_minutes'],
                        'estimated_calories_burned' => $exerciseData['calories_burned'],
                        'intensity' => $exerciseData['intensity'] ?? '보통',
                    ]);
                }
            }
        });
    }

    /**
     * Get target calories for user
     */
    private function getTargetCalories(User $user): float
    {
        $calculation = UserCalculation::where('user_id', $user->id)
            ->latest('calculated_at')
            ->first();

        return $calculation?->target_calories ?? 1500;
    }

    /**
     * Get plan by ID with relationships
     */
    public function getPlanWithDetails(int $planId): ?DietPlan
    {
        return DietPlan::with([
            'dailyMealPlans.mealItems',
            'dailyExercisePlans',
        ])->find($planId);
    }

    /**
     * Get active plan for user
     */
    public function getActivePlan(User $user): ?DietPlan
    {
        return DietPlan::where('user_id', $user->id)
            ->where('status', 'active')
            ->with([
                'dailyMealPlans.mealItems',
                'dailyExercisePlans',
            ])
            ->latest()
            ->first();
    }

    /**
     * Get plan for specific day
     */
    public function getDayPlan(DietPlan $dietPlan, int $dayNumber): array
    {
        $mealPlan = $dietPlan->dailyMealPlans()
            ->where('day_number', $dayNumber)
            ->with('mealItems')
            ->first();

        $exercisePlans = $dietPlan->dailyExercisePlans()
            ->where('day_number', $dayNumber)
            ->get();

        return [
            'day' => $dayNumber,
            'date' => $mealPlan?->date,
            'meals' => $mealPlan,
            'exercises' => $exercisePlans,
        ];
    }

    /**
     * Regenerate an existing plan
     */
    public function regeneratePlan(DietPlan $dietPlan): DietPlan
    {
        // Archive old plan
        $dietPlan->archive();

        // Create new plan
        return $this->generatePlan($dietPlan->user, $dietPlan->survey_response_id);
    }

    /**
     * Replace a meal item with a similar food
     */
    public function replaceMealItem(MealPlanItem $mealItem, ?int $replacementFoodId = null): MealPlanItem
    {
        $oldCalories = $mealItem->calories;
        $oldCategory = $mealItem->food?->category;

        // If specific food provided, use it
        if ($replacementFoodId) {
            $replacementFood = Food::findOrFail($replacementFoodId);
        } else {
            // Find similar food automatically
            $replacementFood = $this->findSimilarFood($oldCalories, $oldCategory, $mealItem->food_id);
        }

        if (!$replacementFood) {
            throw new \Exception('No suitable replacement food found');
        }

        // Calculate serving size to match calories
        $servingRatio = $oldCalories / $replacementFood->calories;
        $newServingSize = $replacementFood->serving_size * $servingRatio;

        // Update meal item
        $mealItem->update([
            'food_id' => $replacementFood->id,
            'food_name' => $replacementFood->name,
            'serving_size' => round($newServingSize, 2),
            'calories' => round($replacementFood->calories * $servingRatio, 2),
            'protein_g' => round($replacementFood->protein_g * $servingRatio, 2),
            'carbs_g' => round($replacementFood->carbs_g * $servingRatio, 2),
            'fat_g' => round($replacementFood->fat_g * $servingRatio, 2),
        ]);

        // Recalculate daily totals
        $mealItem->dailyMealPlan->recalculateTotals();

        return $mealItem->fresh();
    }

    /**
     * Replace an exercise with a similar one
     */
    public function replaceExercise(DailyExercisePlan $exercisePlan, ?int $replacementExerciseId = null): DailyExercisePlan
    {
        $oldIntensity = $exercisePlan->intensity;
        $oldCategory = $exercisePlan->exercise?->category;
        $oldCalories = $exercisePlan->estimated_calories_burned;

        // If specific exercise provided, use it
        if ($replacementExerciseId) {
            $replacementExercise = Exercise::findOrFail($replacementExerciseId);
        } else {
            // Find similar exercise automatically
            $replacementExercise = $this->findSimilarExercise($oldIntensity, $oldCategory, $exercisePlan->exercise_id);
        }

        if (!$replacementExercise) {
            throw new \Exception('No suitable replacement exercise found');
        }

        // Keep similar duration but adjust for different intensity
        $newDuration = $exercisePlan->duration_minutes;

        // Update exercise plan
        $exercisePlan->update([
            'exercise_id' => $replacementExercise->id,
            'exercise_name' => $replacementExercise->name,
            'duration_minutes' => $newDuration,
            'estimated_calories_burned' => round($replacementExercise->calories_per_hour_per_kg * 70 * ($newDuration / 60), 2),
            'intensity' => $replacementExercise->intensity,
        ]);

        return $exercisePlan->fresh();
    }

    /**
     * Find similar food by calories and category
     */
    private function findSimilarFood(float $targetCalories, ?string $category, ?int $excludeFoodId): ?Food
    {
        $query = Food::query();

        // Same category if available
        if ($category) {
            $query->where('category', $category);
        }

        // Exclude current food
        if ($excludeFoodId) {
            $query->where('id', '!=', $excludeFoodId);
        }

        // Find food with similar calories (±20%)
        $minCalories = $targetCalories * 0.8;
        $maxCalories = $targetCalories * 1.2;

        return $query->whereBetween('calories', [$minCalories, $maxCalories])
            ->inRandomOrder()
            ->first();
    }

    /**
     * Find similar exercise by intensity and category
     */
    private function findSimilarExercise(string $intensity, ?string $category, ?int $excludeExerciseId): ?Exercise
    {
        $query = Exercise::query();

        // Same intensity
        $query->where('intensity', $intensity);

        // Same category if available
        if ($category) {
            $query->where('category', $category);
        }

        // Exclude current exercise
        if ($excludeExerciseId) {
            $query->where('id', '!=', $excludeExerciseId);
        }

        return $query->inRandomOrder()->first();
    }

    /**
     * Get replacement suggestions for a meal item
     */
    public function getMealReplacementSuggestions(MealPlanItem $mealItem, int $limit = 5): array
    {
        $targetCalories = $mealItem->calories;
        $category = $mealItem->food?->category;

        $minCalories = $targetCalories * 0.8;
        $maxCalories = $targetCalories * 1.2;

        $query = Food::query()
            ->where('id', '!=', $mealItem->food_id)
            ->whereBetween('calories', [$minCalories, $maxCalories]);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Get replacement suggestions for an exercise
     */
    public function getExerciseReplacementSuggestions(DailyExercisePlan $exercisePlan, int $limit = 5): array
    {
        $intensity = $exercisePlan->intensity;
        $category = $exercisePlan->exercise?->category;

        $query = Exercise::query()
            ->where('id', '!=', $exercisePlan->exercise_id)
            ->where('intensity', $intensity);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->limit($limit)->get()->toArray();
    }
}
