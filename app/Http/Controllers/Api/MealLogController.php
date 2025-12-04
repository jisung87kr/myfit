<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\MealLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MealLogController extends Controller
{
    /**
     * Get meal logs for a specific date
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $meals = MealLog::forUser(auth()->id())
            ->forDate($request->date)
            ->orderBy('meal_time')
            ->orderBy('created_at')
            ->get();

        $groupedMeals = [
            'breakfast' => $meals->where('meal_type', 'breakfast')->values(),
            'lunch' => $meals->where('meal_type', 'lunch')->values(),
            'dinner' => $meals->where('meal_type', 'dinner')->values(),
            'snack' => $meals->where('meal_type', 'snack')->values(),
        ];

        return response()->success('Meal logs retrieved successfully', [
            'date' => $request->date,
            'meals' => $groupedMeals,
            'total_count' => $meals->count(),
        ]);
    }

    /**
     * Store a new meal log
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'food_id' => 'nullable|exists:foods,id',
            'food_name' => 'required_without:food_id|string|max:255',
            'serving_size' => 'required|numeric|min:0.01|max:9999.99',
            'calories' => 'required_without:food_id|numeric|min:0|max:9999.99',
            'protein_g' => 'required_without:food_id|numeric|min:0|max:999.99',
            'carbs_g' => 'required_without:food_id|numeric|min:0|max:999.99',
            'fat_g' => 'required_without:food_id|numeric|min:0|max:999.99',
            'meal_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        // If food_id is provided, auto-fill nutritional data
        if ($request->food_id) {
            $food = Food::find($request->food_id);

            // Calculate nutrients based on serving size ratio
            $servingRatio = $request->serving_size / $food->serving_size;

            $data['food_name'] = $food->name;
            $data['calories'] = round($food->calories * $servingRatio, 2);
            $data['protein_g'] = round($food->protein_g * $servingRatio, 2);
            $data['carbs_g'] = round($food->carbs_g * $servingRatio, 2);
            $data['fat_g'] = round($food->fat_g * $servingRatio, 2);
        }

        $mealLog = MealLog::create($data);

        return response()->success('Meal logged successfully', $mealLog, 201);
    }

    /**
     * Update a meal log
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $mealLog = MealLog::find($id);

        if (!$mealLog) {
            return response()->notFound('Meal log not found');
        }

        // Check authorization
        if ($mealLog->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this meal log');
        }

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date',
            'meal_type' => 'sometimes|required|in:breakfast,lunch,dinner,snack',
            'food_id' => 'nullable|exists:foods,id',
            'food_name' => 'sometimes|required|string|max:255',
            'serving_size' => 'sometimes|required|numeric|min:0.01|max:9999.99',
            'calories' => 'sometimes|required|numeric|min:0|max:9999.99',
            'protein_g' => 'sometimes|required|numeric|min:0|max:999.99',
            'carbs_g' => 'sometimes|required|numeric|min:0|max:999.99',
            'fat_g' => 'sometimes|required|numeric|min:0|max:999.99',
            'meal_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $mealLog->update($validator->validated());

        return response()->success('Meal log updated successfully', $mealLog);
    }

    /**
     * Delete a meal log
     */
    public function destroy(int $id): JsonResponse
    {
        $mealLog = MealLog::find($id);

        if (!$mealLog) {
            return response()->notFound('Meal log not found');
        }

        // Check authorization
        if ($mealLog->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this meal log');
        }

        $mealLog->delete();

        return response()->success('Meal log deleted successfully');
    }

    /**
     * Get daily summary for a specific date
     */
    public function summary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $summary = MealLog::getDailySummary(auth()->id(), $request->date);

        // Get user's target calories if available
        $user = auth()->user();
        $targetCalories = null;

        $calculation = $user->calculations()->latest('calculated_at')->first();
        if ($calculation) {
            $targetCalories = $calculation->target_calories;
            $summary['target_calories'] = $targetCalories;
            $summary['calories_remaining'] = $targetCalories - $summary['total_calories'];
            $summary['percentage'] = $targetCalories > 0
                ? round(($summary['total_calories'] / $targetCalories) * 100, 1)
                : 0;
        }

        return response()->success('Daily summary retrieved successfully', $summary);
    }

    /**
     * Log a meal from plan (quick add)
     */
    public function logFromPlan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'meal_plan_item_id' => 'required|exists:meal_plan_items,id',
            'date' => 'required|date',
            'meal_time' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $mealPlanItem = \App\Models\MealPlanItem::find($request->meal_plan_item_id);

        // Verify user owns the diet plan
        if ($mealPlanItem->dailyMealPlan->dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this meal plan item');
        }

        // Create meal log from plan item
        $mealLog = MealLog::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'meal_type' => $mealPlanItem->meal_type,
            'food_id' => $mealPlanItem->food_id,
            'food_name' => $mealPlanItem->food_name,
            'serving_size' => $mealPlanItem->serving_size,
            'calories' => $mealPlanItem->calories,
            'protein_g' => $mealPlanItem->protein_g,
            'carbs_g' => $mealPlanItem->carbs_g,
            'fat_g' => $mealPlanItem->fat_g,
            'meal_time' => $request->meal_time,
            'notes' => '플랜에서 추가',
        ]);

        return response()->success('Meal logged from plan successfully', $mealLog, 201);
    }
}
