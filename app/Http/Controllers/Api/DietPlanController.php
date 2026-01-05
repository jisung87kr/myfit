<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyExercisePlan;
use App\Models\DietPlan;
use App\Models\MealPlanItem;
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
     * Generate a new diet plan (async)
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'survey_response_id' => 'nullable|exists:user_survey_responses,id',
            'duration_days' => 'nullable|integer|in:7,14,30',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $user = $request->user();

        // Check if user already has an active plan
        $existingPlan = DietPlan::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingPlan) {
            return response()->error(
                'You already have an active diet plan. Please complete or archive it before generating a new one.',
                null,
                409
            );
        }

        // Create initial plan
        $dietPlan = $this->dietPlanService->generatePlan(
            $user,
            $request->survey_response_id,
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
            'start_date' => $dietPlan->start_date,
            'end_date' => $dietPlan->end_date,
            'target_calories_per_day' => $dietPlan->target_calories_per_day,
            'ai_summary' => $dietPlan->ai_summary,
            'daily_meal_plans' => $dietPlan->dailyMealPlans,
            'daily_exercise_plans' => $dietPlan->dailyExercisePlans,
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
