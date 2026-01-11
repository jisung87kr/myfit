<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateDietPlanJob;
use App\Models\DailyExercisePlan;
use App\Models\DietPlan;
use App\Models\MealPlanItem;
use App\Models\Survey;
use App\Models\SurveySubmission;
use App\Services\CalorieCalculationService;
use App\Services\DietPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DietPlanController extends Controller
{
    public function __construct(
        private DietPlanService $dietPlanService,
        private CalorieCalculationService $calorieCalculationService
    ) {}

    /**
     * List all diet plans for the user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $plans = DietPlan::where('user_id', $user->id)
            ->with(['dailyMealPlans', 'dailyExercisePlans'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($plan) {
                // Calculate averages from daily meal plans
                $mealPlans = $plan->dailyMealPlans;
                $avgCalories = $mealPlans->avg('total_calories');
                $avgProtein = $mealPlans->avg('total_protein_g');
                $avgCarbs = $mealPlans->avg('total_carbs_g');
                $avgFat = $mealPlans->avg('total_fat_g');

                // Calculate exercise totals
                $exercisePlans = $plan->dailyExercisePlans;
                $totalExercises = $exercisePlans->count();
                $avgCaloriesBurned = $exercisePlans->avg('estimated_calories_burned');

                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'status' => $plan->status,
                    'duration_days' => $plan->duration_days,
                    'start_date' => $plan->start_date,
                    'end_date' => $plan->end_date,
                    'target_calories_per_day' => $plan->target_calories_per_day,
                    'ai_summary' => $plan->ai_summary,
                    'created_at' => $plan->created_at,
                    'updated_at' => $plan->updated_at,
                    // Aggregated data from daily plans
                    'avg_calories_per_day' => $avgCalories ? round($avgCalories) : null,
                    'avg_protein_g' => $avgProtein ? round($avgProtein) : null,
                    'avg_carbs_g' => $avgCarbs ? round($avgCarbs) : null,
                    'avg_fat_g' => $avgFat ? round($avgFat) : null,
                    'total_exercises' => $totalExercises,
                    'avg_calories_burned' => $avgCaloriesBurned ? round($avgCaloriesBurned) : null,
                ];
            });

        // Check survey completion status
        $surveyStatus = $this->getSurveyCompletionStatus($user);

        return response()->success([
            'diet_plans' => $plans,
            'total' => $plans->count(),
            'survey_status' => $surveyStatus,
        ], 'Diet plans retrieved');
    }

    /**
     * Get survey completion status for user
     */
    private function getSurveyCompletionStatus($user): array
    {
        $survey = Survey::where('is_active', true)->first();

        if (!$survey) {
            return [
                'has_survey' => false,
                'is_completed' => false,
                'survey_id' => null,
            ];
        }

        $submissions = SurveySubmission::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->orderBy('submitted_at', 'desc')
            ->get();

        return [
            'has_survey' => true,
            'is_completed' => $submissions->isNotEmpty(),
            'survey_id' => $survey->id,
            'submissions' => $submissions->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'submitted_at' => $submission->submitted_at,
                    'summary' => $this->getSurveySubmissionSummary($submission),
                ];
            }),
        ];
    }

    /**
     * Get a brief summary of survey submission for display
     */
    private function getSurveySubmissionSummary(SurveySubmission $submission): string
    {
        $data = $submission->completion_data ?? [];

        $parts = [];

        // 목표 추출
        if (isset($data['목표'])) {
            $parts[] = $data['목표'];
        }

        // 활동량 추출
        if (isset($data['활동량'])) {
            $parts[] = '활동량: ' . $data['활동량'];
        }

        if (empty($parts)) {
            return '설문 응답 #' . $submission->id;
        }

        return implode(' / ', $parts);
    }

    /**
     * Generate a new diet plan
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'duration_days' => 'nullable|integer|in:7,14,30',
            'survey_submission_id' => 'nullable|integer|exists:survey_submissions,id',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();

        // Check if user has completed the survey
        $survey = Survey::where('is_active', true)->first();

        if (!$survey) {
            return response()->error('활성화된 설문이 없습니다.', null, 400);
        }

        // Use provided submission_id or get the latest one
        $submissionId = $request->survey_submission_id;

        if ($submissionId) {
            // Verify the submission belongs to this user
            $submission = SurveySubmission::where('id', $submissionId)
                ->where('user_id', $user->id)
                ->first();

            if (!$submission) {
                return response()->error('해당 설문 응답을 찾을 수 없습니다.', null, 404);
            }
        } else {
            // Get the latest submission
            $submission = SurveySubmission::where('user_id', $user->id)
                ->where('survey_id', $survey->id)
                ->orderBy('submitted_at', 'desc')
                ->first();

            if (!$submission) {
                return response()->error(
                    '식단 플랜을 생성하려면 먼저 설문을 완료해야 합니다.',
                    ['survey_id' => $survey->id],
                    400
                );
            }
        }

        // Create initial plan with survey_submission_id
        $dietPlan = $this->dietPlanService->generatePlan(
            $user,
            $submission->id,
            $request->duration_days ?? 7
        );

        // Dispatch job to generate plan asynchronously
        GenerateDietPlanJob::dispatch($dietPlan);

        return response()->success(
            [
                'diet_plan_id' => $dietPlan->id,
                'status' => $dietPlan->status,
            ],
            '식단 플랜 생성이 시작되었습니다. 완료되면 알림을 보내드립니다.'
        );
    }

    /**
     * Delete a diet plan
     */
    public function destroy(int $id): JsonResponse
    {
        $dietPlan = DietPlan::find($id);

        if (!$dietPlan) {
            return response()->notFound('Diet plan not found');
        }

        // Check authorization
        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this diet plan');
        }

        // Delete related records (cascade should handle this, but being explicit)
        $dietPlan->dailyMealPlans()->each(function ($mealPlan) {
            $mealPlan->mealItems()->delete();
            $mealPlan->delete();
        });
        $dietPlan->dailyExercisePlans()->delete();
        $dietPlan->delete();

        return response()->success(null, 'Diet plan deleted successfully');
    }

    /**
     * Update diet plan name
     */
    public function updateName(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $dietPlan = DietPlan::find($id);

        if (!$dietPlan) {
            return response()->notFound('Diet plan not found');
        }

        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this diet plan');
        }

        $dietPlan->update(['name' => $request->name]);

        return response()->success([
            'id' => $dietPlan->id,
            'name' => $dietPlan->name,
        ], '플랜명이 수정되었습니다.');
    }

    /**
     * Check generation status
     */
    public function generationStatus(int $id): JsonResponse
    {
        $dietPlan = DietPlan::find($id);

        if (!$dietPlan) {
            return response()->notFound('Diet plan not found');
        }

        // Check if user owns this plan
        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this diet plan');
        }

        return response()->success([
            'diet_plan_id' => $dietPlan->id,
            'status' => $dietPlan->status,
            'is_generating' => $dietPlan->isGenerating(),
            'is_active' => $dietPlan->isActive(),
            'created_at' => $dietPlan->created_at,
        ], 'Generation status retrieved');
    }

    /**
     * Get active diet plan
     */
    public function getActive(Request $request): JsonResponse
    {
        $activePlan = $this->dietPlanService->getActivePlan($request->user());

        if (!$activePlan) {
            return response()->error('No active diet plan found', null, 404);
        }

        return response()->success([
            'id' => $activePlan->id,
            'status' => $activePlan->status,
            'start_date' => $activePlan->start_date,
            'end_date' => $activePlan->end_date,
            'target_calories_per_day' => $activePlan->target_calories_per_day,
            'ai_summary' => $activePlan->ai_summary,
            'daily_meal_plans' => $activePlan->dailyMealPlans->map(function ($mealPlan) {
                return [
                    'day_number' => $mealPlan->day_number,
                    'date' => $mealPlan->date,
                    'total_calories' => $mealPlan->total_calories,
                    'total_protein_g' => $mealPlan->total_protein_g,
                    'total_carbs_g' => $mealPlan->total_carbs_g,
                    'total_fat_g' => $mealPlan->total_fat_g,
                    'tips' => $mealPlan->tips,
                    'meals' => $this->groupMealsByType($mealPlan->mealItems),
                ];
            }),
            'daily_exercise_plans' => $activePlan->dailyExercisePlans->groupBy('day_number')->map(function ($exercises, $day) {
                return [
                    'day_number' => $day,
                    'exercises' => $exercises->map(function ($exercise) {
                        return [
                            'id' => $exercise->id,
                            'exercise_name' => $exercise->exercise_name,
                            'duration_minutes' => $exercise->duration_minutes,
                            'estimated_calories_burned' => $exercise->estimated_calories_burned,
                            'intensity' => $exercise->intensity,
                            'notes' => $exercise->notes,
                        ];
                    })->values(),
                ];
            })->values(),
        ], 'Active diet plan retrieved');
    }

    /**
     * Get specific diet plan
     */
    public function show(int $id): JsonResponse
    {
        $dietPlan = $this->dietPlanService->getPlanWithDetails($id);

        if (!$dietPlan) {
            return response()->notFound('Diet plan not found');
        }

        // Check authorization
        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this diet plan');
        }

        // Calculate aggregated data from daily plans
        $mealPlans = $dietPlan->dailyMealPlans;
        $avgCalories = $mealPlans->avg('total_calories');
        $avgProtein = $mealPlans->avg('total_protein_g');
        $avgCarbs = $mealPlans->avg('total_carbs_g');
        $avgFat = $mealPlans->avg('total_fat_g');

        $exercisePlans = $dietPlan->dailyExercisePlans;
        $totalExercises = $exercisePlans->count();
        $avgCaloriesBurned = $exercisePlans->avg('estimated_calories_burned') ?? 0;

        // Calculate estimated weight loss
        $estimatedWeightLoss = $this->calculateEstimatedWeightLoss(
            $dietPlan,
            $avgCalories,
            $avgCaloriesBurned
        );

        return response()->success([
            'id' => $dietPlan->id,
            'status' => $dietPlan->status,
            'duration_days' => $dietPlan->duration_days,
            'start_date' => $dietPlan->start_date,
            'end_date' => $dietPlan->end_date,
            'target_calories_per_day' => $dietPlan->target_calories_per_day,
            'ai_summary' => $dietPlan->ai_summary,
            // Aggregated data from daily plans
            'avg_calories_per_day' => $avgCalories ? round($avgCalories) : null,
            'avg_protein_g' => $avgProtein ? round($avgProtein) : null,
            'avg_carbs_g' => $avgCarbs ? round($avgCarbs) : null,
            'avg_fat_g' => $avgFat ? round($avgFat) : null,
            'total_exercises' => $totalExercises,
            'avg_calories_burned' => $avgCaloriesBurned ? round($avgCaloriesBurned) : null,
            'estimated_weight_loss_kg' => $estimatedWeightLoss,
            'daily_meal_plans' => $dietPlan->dailyMealPlans->map(function ($mealPlan) {
                return [
                    'day_number' => $mealPlan->day_number,
                    'date' => $mealPlan->date,
                    'total_calories' => $mealPlan->total_calories,
                    'total_protein_g' => $mealPlan->total_protein_g,
                    'total_carbs_g' => $mealPlan->total_carbs_g,
                    'total_fat_g' => $mealPlan->total_fat_g,
                    'tips' => $mealPlan->tips,
                    'meals' => $this->groupMealsByType($mealPlan->mealItems),
                ];
            }),
            'daily_exercise_plans' => $dietPlan->dailyExercisePlans->groupBy('day_number')->map(function ($exercises, $day) {
                return [
                    'day_number' => $day,
                    'exercises' => $exercises->map(function ($exercise) {
                        return [
                            'id' => $exercise->id,
                            'exercise_name' => $exercise->exercise_name,
                            'duration_minutes' => $exercise->duration_minutes,
                            'estimated_calories_burned' => $exercise->estimated_calories_burned,
                            'intensity' => $exercise->intensity,
                            'notes' => $exercise->notes,
                        ];
                    })->values(),
                ];
            })->values(),
        ], 'Diet plan retrieved');
    }

    /**
     * Get plan for specific day
     */
    public function showDay(int $id, int $day): JsonResponse
    {
        $dietPlan = DietPlan::find($id);

        if (!$dietPlan) {
            return response()->notFound('Diet plan not found');
        }

        // Check authorization
        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this diet plan');
        }

        // Validate day number
        $maxDays = $dietPlan->duration_days ?? 7;
        if ($day < 1 || $day > $maxDays) {
            return response()->error("Invalid day number. Must be between 1 and {$maxDays}.", null, 400);
        }

        $dayPlan = $this->dietPlanService->getDayPlan($dietPlan, $day);

        if (!$dayPlan['meals']) {
            return response()->error('No meal plan found for this day', null, 404);
        }

        return response()->success([
            'day_number' => $day,
            'date' => $dayPlan['date'],
            'meals' => $this->groupMealsByType($dayPlan['meals']->mealItems),
            'total_calories' => $dayPlan['meals']->total_calories,
            'total_protein_g' => $dayPlan['meals']->total_protein_g,
            'total_carbs_g' => $dayPlan['meals']->total_carbs_g,
            'total_fat_g' => $dayPlan['meals']->total_fat_g,
            'tips' => $dayPlan['meals']->tips,
            'exercises' => $dayPlan['exercises']->map(function ($exercise) {
                return [
                    'id' => $exercise->id,
                    'exercise_name' => $exercise->exercise_name,
                    'duration_minutes' => $exercise->duration_minutes,
                    'estimated_calories_burned' => $exercise->estimated_calories_burned,
                    'intensity' => $exercise->intensity,
                    'notes' => $exercise->notes,
                ];
            }),
        ], 'Day plan retrieved');
    }

    /**
     * Regenerate diet plan
     */
    public function regenerate(int $id): JsonResponse
    {
        $dietPlan = DietPlan::find($id);

        if (!$dietPlan) {
            return response()->notFound('Diet plan not found');
        }

        // Check authorization
        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this diet plan');
        }

        // Create new plan
        $newPlan = $this->dietPlanService->regeneratePlan($dietPlan);

        try {
            // Generate the plan with AI directly
            $this->dietPlanService->generateWithAI($newPlan);

            return response()->success(
                [
                    'old_plan_id' => $dietPlan->id,
                    'new_plan_id' => $newPlan->id,
                    'status' => $newPlan->status,
                ],
                'Diet plan regenerated successfully'
            );
        } catch (\Exception $e) {
            $newPlan->markAsFailed();
            return response()->error('Failed to regenerate diet plan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Replace a meal item
     */
    public function replaceMealItem(Request $request, int $mealItemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'replacement_food_id' => 'nullable|exists:foods,id',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $mealItem = MealPlanItem::find($mealItemId);

        if (!$mealItem) {
            return response()->notFound('Meal item not found');
        }

        // Check authorization
        $dietPlan = $mealItem->dailyMealPlan->dietPlan;
        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this meal item');
        }

        try {
            $updatedItem = $this->dietPlanService->replaceMealItem(
                $mealItem,
                $request->replacement_food_id
            );

            return response()->success([
                'meal_item' => $this->formatMealItem($updatedItem),
                'daily_totals' => [
                    'total_calories' => $updatedItem->dailyMealPlan->total_calories,
                    'total_protein_g' => $updatedItem->dailyMealPlan->total_protein_g,
                    'total_carbs_g' => $updatedItem->dailyMealPlan->total_carbs_g,
                    'total_fat_g' => $updatedItem->dailyMealPlan->total_fat_g,
                ],
            ], 'Meal item replaced successfully');
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), null, 400);
        }
    }

    /**
     * Get replacement suggestions for a meal item
     */
    public function getMealReplacementSuggestions(int $mealItemId): JsonResponse
    {
        $mealItem = MealPlanItem::find($mealItemId);

        if (!$mealItem) {
            return response()->notFound('Meal item not found');
        }

        // Check authorization
        $dietPlan = $mealItem->dailyMealPlan->dietPlan;
        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this meal item');
        }

        $suggestions = $this->dietPlanService->getMealReplacementSuggestions($mealItem);

        return response()->success($suggestions, 'Replacement suggestions retrieved');
    }

    /**
     * Replace an exercise
     */
    public function replaceExercise(Request $request, int $exerciseId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'replacement_exercise_id' => 'nullable|exists:exercises,id',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $exercisePlan = DailyExercisePlan::find($exerciseId);

        if (!$exercisePlan) {
            return response()->notFound('Exercise plan not found');
        }

        // Check authorization
        if ($exercisePlan->dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this exercise plan');
        }

        try {
            $updatedExercise = $this->dietPlanService->replaceExercise(
                $exercisePlan,
                $request->replacement_exercise_id
            );

            return response()->success([
                'id' => $updatedExercise->id,
                'exercise_name' => $updatedExercise->exercise_name,
                'duration_minutes' => $updatedExercise->duration_minutes,
                'estimated_calories_burned' => $updatedExercise->estimated_calories_burned,
                'intensity' => $updatedExercise->intensity,
            ], 'Exercise replaced successfully');
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), null, 400);
        }
    }

    /**
     * Get replacement suggestions for an exercise
     */
    public function getExerciseReplacementSuggestions(int $exerciseId): JsonResponse
    {
        $exercisePlan = DailyExercisePlan::find($exerciseId);

        if (!$exercisePlan) {
            return response()->notFound('Exercise plan not found');
        }

        // Check authorization
        if ($exercisePlan->dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this exercise plan');
        }

        $suggestions = $this->dietPlanService->getExerciseReplacementSuggestions($exercisePlan);

        return response()->success($suggestions, 'Replacement suggestions retrieved');
    }

    /**
     * Helper to group meals by type
     */
    private function groupMealsByType($mealItems): array
    {
        $grouped = $mealItems->groupBy('meal_type');

        return [
            'breakfast' => $grouped->get('breakfast', collect())->map(fn($item) => $this->formatMealItem($item))->values(),
            'lunch' => $grouped->get('lunch', collect())->map(fn($item) => $this->formatMealItem($item))->values(),
            'dinner' => $grouped->get('dinner', collect())->map(fn($item) => $this->formatMealItem($item))->values(),
            'snack' => $grouped->get('snack', collect())->map(fn($item) => $this->formatMealItem($item))->values(),
        ];
    }

    /**
     * Format meal item
     */
    private function formatMealItem($item): array
    {
        return [
            'id' => $item->id,
            'food_name' => $item->food_name,
            'serving_size' => $item->serving_size,
            'calories' => $item->calories,
            'protein_g' => $item->protein_g,
            'carbs_g' => $item->carbs_g,
            'fat_g' => $item->fat_g,
            'notes' => $item->notes,
        ];
    }

    /**
     * Calculate estimated weight loss for a diet plan
     *
     * Formula:
     * - Daily deficit = TDEE - avg_calories_intake + avg_exercise_burned
     * - Total deficit = daily_deficit * duration_days
     * - Weight loss (kg) = total_deficit / 7700 (7700 kcal ≈ 1kg)
     *
     * @param DietPlan $dietPlan
     * @param float|null $avgCaloriesIntake
     * @param float $avgCaloriesBurned
     * @return float|null
     */
    private function calculateEstimatedWeightLoss(
        DietPlan $dietPlan,
        ?float $avgCaloriesIntake,
        float $avgCaloriesBurned = 0
    ): ?float {
        // Need avg calories intake to calculate
        if (!$avgCaloriesIntake) {
            return null;
        }

        // Get user's TDEE from survey submission
        $submission = $dietPlan->surveySubmission;
        if (!$submission) {
            return null;
        }

        $surveyData = $submission->completion_data ?? [];

        // Extract required data for TDEE calculation
        $gender = $surveyData['성별'] ?? null;
        $age = isset($surveyData['나이']) ? (int) $surveyData['나이'] : null;
        $weight = isset($surveyData['현재 체중 (kg)']) ? (float) $surveyData['현재 체중 (kg)'] : null;
        $height = isset($surveyData['키 (cm)']) ? (float) $surveyData['키 (cm)'] : null;
        $activityLevel = $surveyData['일일 활동량'] ?? null;

        // All required fields must be present
        if (!$gender || !$age || !$weight || !$height || !$activityLevel) {
            return null;
        }

        // Map activity level to factor
        $activityMapping = [
            '거의 운동 안 함' => 'sedentary',
            '좌식 생활' => 'sedentary',
            '주 1-3회 가벼운 운동' => 'lightly_active',
            '가벼운 활동' => 'lightly_active',
            '주 3-5회 중간 강도 운동' => 'moderately_active',
            '보통 활동' => 'moderately_active',
            '주 6-7회 고강도 운동' => 'very_active',
            '매우 활동적' => 'very_active',
            '하루 2회 이상 운동' => 'extra_active',
        ];

        $mappedActivityLevel = $activityMapping[$activityLevel] ?? 'sedentary';

        // Calculate BMR and TDEE
        $bmr = $this->calorieCalculationService->calculateBMR($gender, $weight, $height, $age);
        $tdee = $this->calorieCalculationService->calculateTDEE($bmr, $mappedActivityLevel);

        // Calculate daily calorie deficit
        // Deficit = TDEE - calories eaten + calories burned from exercise
        $dailyDeficit = $tdee - $avgCaloriesIntake + $avgCaloriesBurned;

        // If no deficit (surplus), return negative value (weight gain)
        $durationDays = $dietPlan->duration_days ?? 7;
        $totalDeficit = $dailyDeficit * $durationDays;

        // 7700 kcal ≈ 1 kg of body fat
        $weightLossKg = $totalDeficit / 7700;

        return round($weightLossKg, 2);
    }

    /**
     * Get calendar data for a diet plan
     */
    public function getCalendar(int $id): JsonResponse
    {
        $dietPlan = DietPlan::with(['dailyMealPlans', 'dailyExercisePlans'])
            ->find($id);

        if (!$dietPlan) {
            return response()->notFound('Diet plan not found');
        }

        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this diet plan');
        }

        $days = $dietPlan->dailyMealPlans->map(function ($mealPlan) use ($dietPlan) {
            $hasExercise = $dietPlan->dailyExercisePlans
                ->where('day_number', $mealPlan->day_number)
                ->isNotEmpty();

            return [
                'day_number' => $mealPlan->day_number,
                'date' => $mealPlan->date->format('Y-m-d'),
                'total_calories' => round($mealPlan->total_calories),
                'meal_completed' => $mealPlan->isMealCompleted(),
                'meal_completed_at' => $mealPlan->meal_completed_at,
                'exercise_completed' => $mealPlan->isExerciseCompleted(),
                'exercise_completed_at' => $mealPlan->exercise_completed_at,
                'has_exercise' => $hasExercise,
            ];
        });

        // Calculate statistics
        $completedDays = $days->filter(function ($d) {
            return $d['meal_completed'] && (!$d['has_exercise'] || $d['exercise_completed']);
        })->count();

        return response()->success([
            'plan_id' => $dietPlan->id,
            'start_date' => $dietPlan->start_date,
            'end_date' => $dietPlan->end_date,
            'duration_days' => $dietPlan->duration_days,
            'days' => $days->values(),
            'statistics' => [
                'total_days' => $days->count(),
                'completed_days' => $completedDays,
                'completion_rate' => $days->count() > 0 ? round(($completedDays / $days->count()) * 100, 1) : 0,
                'current_streak' => $this->calculateCurrentStreak($days),
                'longest_streak' => $this->calculateLongestStreak($days),
            ],
        ], 'Calendar data retrieved');
    }

    /**
     * Toggle day completion
     */
    public function toggleDayCompletion(Request $request, int $planId, int $dayNumber): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:meal,exercise',
            'completed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $dietPlan = DietPlan::find($planId);

        if (!$dietPlan) {
            return response()->notFound('Diet plan not found');
        }

        if ($dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this diet plan');
        }

        $dailyMealPlan = $dietPlan->dailyMealPlans()
            ->where('day_number', $dayNumber)
            ->first();

        if (!$dailyMealPlan) {
            return response()->notFound('Day plan not found');
        }

        $field = $request->type === 'meal' ? 'meal_completed_at' : 'exercise_completed_at';
        $dailyMealPlan->update([
            $field => $request->completed ? now() : null,
        ]);

        $message = $request->type === 'meal'
            ? ($request->completed ? '식단 완료 처리되었습니다.' : '식단 완료가 취소되었습니다.')
            : ($request->completed ? '운동 완료 처리되었습니다.' : '운동 완료가 취소되었습니다.');

        return response()->success([
            'day_number' => $dayNumber,
            'meal_completed' => $dailyMealPlan->isMealCompleted(),
            'meal_completed_at' => $dailyMealPlan->meal_completed_at,
            'exercise_completed' => $dailyMealPlan->isExerciseCompleted(),
            'exercise_completed_at' => $dailyMealPlan->exercise_completed_at,
        ], $message);
    }

    /**
     * Calculate current streak of completed days
     */
    private function calculateCurrentStreak($days): int
    {
        $sortedDays = $days->sortByDesc('date')->values();
        $streak = 0;

        foreach ($sortedDays as $day) {
            $isCompleted = $day['meal_completed'] && (!$day['has_exercise'] || $day['exercise_completed']);
            if ($isCompleted) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calculate longest streak of completed days
     */
    private function calculateLongestStreak($days): int
    {
        $sortedDays = $days->sortBy('date')->values();
        $longestStreak = 0;
        $currentStreak = 0;

        foreach ($sortedDays as $day) {
            $isCompleted = $day['meal_completed'] && (!$day['has_exercise'] || $day['exercise_completed']);
            if ($isCompleted) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 0;
            }
        }

        return $longestStreak;
    }
}
