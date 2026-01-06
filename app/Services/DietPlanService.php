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
    private const DEFAULT_PLAN_DURATION_DAYS = 7;
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';

    /**
     * Generate a new diet plan for a user
     */
    public function generatePlan(User $user, ?int $surveySubmissionId = null, int $durationDays = self::DEFAULT_PLAN_DURATION_DAYS): DietPlan
    {

        // Create initial plan with 'generating' status
        $dietPlan = DietPlan::create([
            'user_id' => $user->id,
            'survey_submission_id' => $surveySubmissionId,
            'status' => 'generating',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays($durationDays - 1),
            'duration_days' => $durationDays,
            'target_calories_per_day' => $this->getTargetCalories($user),
        ]);

        return $dietPlan;
    }

    private const CHUNK_SIZE_DAYS = 7;

    /**
     * Generate plan using GPT API
     */
    public function generateWithAI(DietPlan $dietPlan): void
    {
        try {
            $userData = $this->getUserData($dietPlan);
            $durationDays = $dietPlan->duration_days ?? self::DEFAULT_PLAN_DURATION_DAYS;

            // For plans longer than CHUNK_SIZE_DAYS, split into chunks
            if ($durationDays > self::CHUNK_SIZE_DAYS) {
                $this->generateWithAIChunked($dietPlan, $userData, $durationDays);
            } else {
                $this->generateWithAISingle($dietPlan, $userData, $durationDays);
            }

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
     * Generate plan with a single API call (for short plans)
     */
    private function generateWithAISingle(DietPlan $dietPlan, array $userData, int $durationDays): void
    {
        $prompt = $this->buildPrompt($userData, $dietPlan, 1, $durationDays);
        $dietPlan->update(['generation_prompt' => $prompt]);

        $response = $this->callGPTAPI($prompt, $durationDays);
        $this->savePlanFromAIResponse($dietPlan, $response);
    }

    /**
     * Generate plan with multiple API calls (for long plans)
     */
    private function generateWithAIChunked(DietPlan $dietPlan, array $userData, int $durationDays): void
    {
        $allDailyPlans = [];
        $summary = '';
        $prompts = [];

        // Split into chunks
        $chunks = $this->calculateChunks($durationDays);

        Log::info('Generating chunked diet plan', [
            'diet_plan_id' => $dietPlan->id,
            'total_days' => $durationDays,
            'chunks' => count($chunks),
        ]);

        foreach ($chunks as $index => $chunk) {
            $startDay = $chunk['start'];
            $endDay = $chunk['end'];
            $chunkDays = $endDay - $startDay + 1;

            Log::info("Processing chunk", [
                'chunk' => $index + 1,
                'start_day' => $startDay,
                'end_day' => $endDay,
            ]);

            $prompt = $this->buildPrompt($userData, $dietPlan, $startDay, $endDay);
            $prompts[] = "=== Chunk {$index} (Days {$startDay}-{$endDay}) ===\n{$prompt}";

            $response = $this->callGPTAPI($prompt, $chunkDays);

            // Collect summary from first chunk only
            if ($index === 0 && isset($response['summary'])) {
                $summary = $response['summary'];
            }

            // Merge daily plans
            if (isset($response['daily_plans']) && is_array($response['daily_plans'])) {
                foreach ($response['daily_plans'] as $dayPlan) {
                    $allDailyPlans[] = $dayPlan;
                }
            }
        }

        // Save combined prompts
        $dietPlan->update(['generation_prompt' => implode("\n\n", $prompts)]);

        // Save combined response
        $combinedResponse = [
            'summary' => $summary,
            'daily_plans' => $allDailyPlans,
        ];

        $this->savePlanFromAIResponse($dietPlan, $combinedResponse);
    }

    /**
     * Calculate chunk ranges for the given duration
     */
    private function calculateChunks(int $totalDays): array
    {
        $chunks = [];
        $currentDay = 1;

        while ($currentDay <= $totalDays) {
            $endDay = min($currentDay + self::CHUNK_SIZE_DAYS - 1, $totalDays);
            $chunks[] = [
                'start' => $currentDay,
                'end' => $endDay,
            ];
            $currentDay = $endDay + 1;
        }

        return $chunks;
    }

    /**
     * Get user data from survey submission linked to diet plan
     */
    private function getUserData(DietPlan $dietPlan): array
    {
        // Get survey submission data from diet plan's linked submission
        $submission = $dietPlan->surveySubmission;
        $surveyResponses = $submission?->completion_data ?? [];

        return [
            'survey_responses' => $surveyResponses,
        ];
    }

    /**
     * Build GPT prompt for a specific day range
     */
    private function buildPrompt(array $userData, DietPlan $dietPlan, int $startDay = 1, int $endDay = 7): string
    {
        // Helper to safely convert any value to string
        $toString = fn($value, $default = '') => is_array($value)
            ? implode(', ', array_filter($value))
            : (string) ($value ?: $default);

        $durationDays = $endDay - $startDay + 1;
        $startDate = $dietPlan->start_date->copy()->addDays($startDay - 1)->format('Y-m-d');

        // Build survey responses section dynamically
        $surveyResponses = $userData['survey_responses'] ?? [];
        $surveySection = $this->buildSurveySection($surveyResponses, $toString);

        return <<<PROMPT
당신은 영양학과 운동 전문가입니다. 다음 사용자 설문 응답을 분석하여 {$durationDays}일간의 맞춤형 다이어트 플랜을 작성해주세요.

[사용자 설문 응답]
{$surveySection}

[플랜 생성 지침]
1. 위 설문 응답을 바탕으로 사용자에게 적합한 일일 목표 칼로리와 영양소 비율을 도출하세요
2. BMR(기초대사량)과 활동량을 고려하여 현실적인 목표를 설정하세요
3. 사용자의 목표(체중 감량/증량/유지)에 맞는 칼로리 적자/잉여를 반영하세요

[플랜 요구사항]
1. {$startDay}일차부터 {$endDay}일차까지의 일별 식단 (아침, 점심, 저녁, 간식)
2. 각 끼니별 음식명, 1인분량(g), 칼로리 및 영양소 (단백질, 탄수화물, 지방)
3. 운동 스케줄 (운동명, 시간, 강도, 예상 소모 칼로리) - 주 3-5회, 나머지 날은 휴식
4. 실천 가능한 팁
5. 사용자의 선호도와 제한사항을 반영한 현실적인 플랜

[응답 형식: 반드시 아래 JSON 형식으로만 응답하세요]
{
  "summary": "플랜 요약 및 근거 (200자 이내)",
  "target_calories": 1500,
  "target_protein_g": 112,
  "target_carbs_g": 150,
  "target_fat_g": 50,
  "daily_plans": [
    {
      "day": {$startDay},
      "date": "{$startDate}",
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
  ]
}

중요:
- 반드시 위 JSON 형식만 응답하세요
- target_calories, target_protein_g, target_carbs_g, target_fat_g는 설문 응답을 분석하여 적절한 값을 도출하세요
- daily_plans 배열에 {$startDay}일차부터 {$endDay}일차까지 모든 날짜를 포함해야 합니다
- day 값은 {$startDay}부터 {$endDay}까지 순차적으로 증가해야 합니다
- 각 음식의 영양소 정보는 정확해야 합니다
- 일일 총 칼로리는 도출한 목표 칼로리의 ±10% 범위 내로 유지하세요
- 운동 강도(intensity)는 반드시 "낮음", "보통", "높음" 중 하나만 사용하세요
- 운동은 주 3-5회만 포함하세요 (나머지 날의 exercise는 null)
PROMPT;
    }

    /**
     * Build survey section for prompt dynamically from survey responses
     */
    private function buildSurveySection(array $surveyResponses, callable $toString): string
    {
        if (empty($surveyResponses)) {
            return "- 설문 응답 없음";
        }

        $lines = [];
        foreach ($surveyResponses as $question => $answer) {
            $formattedAnswer = $toString($answer, '미응답');
            $lines[] = "- {$question}: {$formattedAnswer}";
        }

        return implode("\n", $lines);
    }

    /**
     * Call OpenAI GPT API
     */
    private function callGPTAPI(string $prompt, int $durationDays = 7): array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        // Calculate max tokens based on plan duration
        // Each day needs ~800-1000 tokens for meals + exercise + tips
        $maxTokens = min(16384, max(8000, $durationDays * 1000));

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(600)->post(self::OPENAI_API_URL, [
            'model' => 'gpt-4.1-mini',
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
            'max_tokens' => $maxTokens,
        ]);

        if (!$response->successful()) {
            throw new \Exception('GPT API call failed: ' . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            throw new \Exception('No content in GPT response');
        }

        // Sanitize JSON before parsing (fix common GPT errors like trailing commas)

        Log::info('Raw GPT response content', ['content' => $content]);

        //$content = $this->sanitizeJsonResponse($content);

        $decoded = json_decode($content, true);
        $jsonError = json_last_error();
        $jsonErrorMsg = json_last_error_msg();

        if ($decoded === null || $jsonError !== JSON_ERROR_NONE) {
            Log::error('Failed to parse GPT response as JSON', [
                'error' => $jsonErrorMsg,
                'error_code' => $jsonError,
                'content_length' => strlen($content),
                'content_preview' => substr($content, 0, 500),
                'content_tail' => substr($content, -200),
            ]);
            throw new \Exception('Invalid JSON in GPT response: ' . $jsonErrorMsg);
        }

        if (!isset($decoded['daily_plans']) || !is_array($decoded['daily_plans'])) {
            Log::error('GPT response missing daily_plans array', [
                'keys' => array_keys($decoded ?? []),
            ]);
            throw new \Exception('GPT response missing required daily_plans array');
        }

        return $decoded;
    }

    /**
     * Sanitize JSON response to fix common GPT errors
     */
    private function sanitizeJsonResponse(string $json): string
    {
        // Remove trailing commas before ] or }
        // This handles cases like: "value",} or "value",]
        $json = preg_replace('/,\s*([\]}])/s', '$1', $json);

        // Remove JavaScript-style comments that GPT sometimes includes
        $json = preg_replace('/\/\/[^\n]*\n/', '', $json);
        $json = preg_replace('/\/\*.*?\*\//s', '', $json);

        // Handle truncated JSON by attempting to close unclosed brackets/braces
        $openBraces = substr_count($json, '{') - substr_count($json, '}');
        $openBrackets = substr_count($json, '[') - substr_count($json, ']');

        if ($openBraces > 0 || $openBrackets > 0) {
            Log::warning('GPT response appears truncated, attempting to close brackets', [
                'open_braces' => $openBraces,
                'open_brackets' => $openBrackets,
            ]);

            // Remove any trailing comma before closing
            $json = rtrim($json);
            $json = rtrim($json, ',');

            // Close brackets in LIFO order (this is a simple approach)
            $json .= str_repeat(']', $openBrackets);
            $json .= str_repeat('}', $openBraces);
        }

        return $json;
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
                    if (isset($dayData['meals'][$mealType]) && is_array($dayData['meals'][$mealType])) {
                        foreach ($dayData['meals'][$mealType] as $food) {
                            $totalCalories += $food['calories'] ?? 0;
                            $totalProtein += $food['protein_g'] ?? 0;
                            $totalCarbs += $food['carbs_g'] ?? 0;
                            $totalFat += $food['fat_g'] ?? 0;
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
                    if (isset($dayData['meals'][$mealType]) && is_array($dayData['meals'][$mealType])) {
                        $order = 0;
                        foreach ($dayData['meals'][$mealType] as $foodData) {
                            // Skip if food_name is missing
                            if (empty($foodData['food_name'])) {
                                continue;
                            }

                            // Try to find matching food in database
                            $food = Food::where('name', $foodData['food_name'])->first();

                            MealPlanItem::create([
                                'daily_meal_plan_id' => $dailyMealPlan->id,
                                'meal_type' => $mealType,
                                'food_id' => $food?->id,
                                'food_name' => $foodData['food_name'],
                                'serving_size' => $foodData['serving_size'] ?? 100,
                                'calories' => $foodData['calories'] ?? 0,
                                'protein_g' => $foodData['protein_g'] ?? 0,
                                'carbs_g' => $foodData['carbs_g'] ?? 0,
                                'fat_g' => $foodData['fat_g'] ?? 0,
                                'order' => $order++,
                            ]);
                        }
                    }
                }

                // Save exercise plan (if exists)
                if (isset($dayData['exercise']) && is_array($dayData['exercise']) && !empty($dayData['exercise']['exercise_name'])) {
                    $exerciseData = $dayData['exercise'];

                    // Try to find matching exercise
                    $exercise = Exercise::where('name', $exerciseData['exercise_name'])->first();

                    // Map intensity to valid enum values: 낮음, 보통, 높음
                    $intensityMap = [
                        '낮음' => '낮음', '저' => '낮음', '낮은' => '낮음', 'low' => '낮음',
                        '보통' => '보통', '중' => '보통', '중간' => '보통', 'medium' => '보통', 'moderate' => '보통',
                        '높음' => '높음', '고' => '높음', '높은' => '높음', 'high' => '높음',
                    ];
                    $rawIntensity = $exerciseData['intensity'] ?? '보통';
                    $intensity = $intensityMap[$rawIntensity] ?? '보통';

                    DailyExercisePlan::create([
                        'diet_plan_id' => $dietPlan->id,
                        'day_number' => $dayNumber,
                        'date' => $date,
                        'exercise_id' => $exercise?->id,
                        'exercise_name' => $exerciseData['exercise_name'],
                        'duration_minutes' => $exerciseData['duration_minutes'] ?? 30,
                        'estimated_calories_burned' => $exerciseData['calories_burned'] ?? 0,
                        'intensity' => $intensity,
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

        // Create new plan with same duration
        return $this->generatePlan(
            $dietPlan->user,
            $dietPlan->survey_submission_id,
            $dietPlan->duration_days ?? self::DEFAULT_PLAN_DURATION_DAYS
        );
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
