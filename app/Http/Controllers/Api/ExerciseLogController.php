<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExerciseLogController extends Controller
{
    /**
     * Get exercise logs for a specific date
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $exercises = ExerciseLog::forUser(auth()->id())
            ->forDate($request->date)
            ->orderBy('exercise_time')
            ->orderBy('created_at')
            ->get();

        return response()->success([
            'date' => $request->date,
            'exercises' => $exercises,
            'total_count' => $exercises->count(),
        ], 'Exercise logs retrieved successfully');
    }

    /**
     * Get a specific exercise log
     */
    public function show(int $id): JsonResponse
    {
        $exerciseLog = ExerciseLog::find($id);

        if (!$exerciseLog) {
            return response()->notFound('Exercise log not found');
        }

        // Check authorization
        if ($exerciseLog->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this exercise log');
        }

        return response()->success($exerciseLog, 'Exercise log retrieved successfully');
    }

    /**
     * Store a new exercise log
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'exercise_id' => 'nullable|exists:exercises,id',
            'exercise_name' => 'required_without:exercise_id|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:999',
            'calories_burned' => 'required_without:exercise_id|numeric|min:0|max:9999.99',
            'intensity' => 'nullable|in:낮음,보통,높음,매우 높음',
            'exercise_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        // If exercise_id is provided, auto-fill data and calculate calories
        if ($request->exercise_id) {
            $exercise = Exercise::find($request->exercise_id);

            $data['exercise_name'] = $exercise->name;
            $data['intensity'] = $exercise->intensity;

            // Calculate calories: MET * weight(kg) * duration(hours)
            $user = auth()->user();
            $userWeight = $user->weight ?? 70; // Default to 70kg if no weight

            $durationHours = $request->duration_minutes / 60;
            $data['calories_burned'] = round($exercise->met_value * $userWeight * $durationHours, 2);
        }

        $exerciseLog = ExerciseLog::create($data);

        return response()->created($exerciseLog, 'Exercise logged successfully');
    }

    /**
     * Update an exercise log
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $exerciseLog = ExerciseLog::find($id);

        if (!$exerciseLog) {
            return response()->notFound('Exercise log not found');
        }

        // Check authorization
        if ($exerciseLog->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this exercise log');
        }

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date',
            'exercise_name' => 'sometimes|required|string|max:255',
            'duration_minutes' => 'sometimes|required|integer|min:1|max:999',
            'calories_burned' => 'sometimes|required|numeric|min:0|max:9999.99',
            'intensity' => 'nullable|in:낮음,보통,높음,매우 높음',
            'exercise_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $exerciseLog->update($validator->validated());

        return response()->success($exerciseLog, 'Exercise log updated successfully');
    }

    /**
     * Delete an exercise log
     */
    public function destroy(int $id): JsonResponse
    {
        $exerciseLog = ExerciseLog::find($id);

        if (!$exerciseLog) {
            return response()->notFound('Exercise log not found');
        }

        // Check authorization
        if ($exerciseLog->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this exercise log');
        }

        $exerciseLog->delete();

        return response()->success(null, 'Exercise log deleted successfully');
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

        $summary = ExerciseLog::getDailySummary(auth()->id(), $request->date);

        return response()->success($summary, 'Daily exercise summary retrieved successfully');
    }

    /**
     * Log an exercise from plan (quick add)
     */
    public function logFromPlan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'daily_exercise_plan_id' => 'required|exists:daily_exercise_plans,id',
            'date' => 'required|date',
            'exercise_time' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $dailyExercisePlan = \App\Models\DailyExercisePlan::find($request->daily_exercise_plan_id);

        // Verify user owns the diet plan
        if ($dailyExercisePlan->dietPlan->user_id !== auth()->id()) {
            return response()->forbidden('You do not have access to this exercise plan');
        }

        // Create exercise log from plan
        $exerciseLog = ExerciseLog::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'exercise_id' => $dailyExercisePlan->exercise_id,
            'exercise_name' => $dailyExercisePlan->exercise_name,
            'duration_minutes' => $dailyExercisePlan->duration_minutes,
            'calories_burned' => $dailyExercisePlan->estimated_calories_burned,
            'intensity' => $dailyExercisePlan->intensity,
            'exercise_time' => $request->exercise_time,
            'notes' => '플랜에서 추가',
        ]);

        return response()->created($exerciseLog, 'Exercise logged from plan successfully');
    }
}
