<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyExercisePlan;
use App\Models\DietPlan;
use App\Models\MealPlanItem;
use App\Models\Survey;
use App\Models\SurveySubmission;
use App\Services\DietPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DietPlanController extends Controller
{
    public function __construct(
        private DietPlanService $dietPlanService
    ) {}

    /**
     * List all diet plans for the user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $plans = DietPlan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'status' => $plan->status,
                    'duration_days' => $plan->duration_days,
                    'start_date' => $plan->start_date,
                    'end_date' => $plan->end_date,
                    'target_calories_per_day' => $plan->target_calories_per_day,
                    'ai_summary' => $plan->ai_summary,
                    'created_at' => $plan->created_at,
                    'updated_at' => $plan->updated_at,
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

        $submission = SurveySubmission::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->first();

        return [
            'has_survey' => true,
            'is_completed' => $submission !== null,
            'survey_id' => $survey->id,
            'submission_id' => $submission?->id,
        ];
    }

    /**
     * Generate a new diet plan
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'duration_days' => 'nullable|integer|in:7,14,30',
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

        $submission = SurveySubmission::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->first();

        if (!$submission) {
            return response()->error(
                '식단 플랜을 생성하려면 먼저 설문을 완료해야 합니다.',
                ['survey_id' => $survey->id],
                400
            );
        }

        // Get the latest survey response ID for this user
        $surveyResponse = $user->surveyResponses()->latest()->first();

        // Create initial plan
        $dietPlan = $this->dietPlanService->generatePlan(
            $user,
            $surveyResponse?->id,
            $request->duration_days ?? 7
        );

        try {
            // Generate the plan with AI directly
            $this->dietPlanService->generateWithAI($dietPlan);

            return response()->success(
                [
                    'diet_plan_id' => $dietPlan->id,
                    'status' => $dietPlan->status,
                ],
                'Diet plan generated successfully'
            );
        } catch (\Exception $e) {
            $dietPlan->markAsFailed();
            return response()->error('Failed to generate diet plan: ' . $e->getMessage(), null, 500);
        }
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

        return response()->success([
            'id' => $dietPlan->id,
            'status' => $dietPlan->status,
            'duration_days' => $dietPlan->duration_days,
            'start_date' => $dietPlan->start_date,
            'end_date' => $dietPlan->end_date,
            'target_calories_per_day' => $dietPlan->target_calories_per_day,
            'ai_summary' => $dietPlan->ai_summary,
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
}
