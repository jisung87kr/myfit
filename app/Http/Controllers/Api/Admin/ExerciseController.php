<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExerciseController extends Controller
{
    /**
     * Get paginated list of exercises
     */
    public function index(Request $request): JsonResponse
    {
        $query = Exercise::query();

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by intensity
        if ($request->has('intensity')) {
            $query->where('intensity', $request->intensity);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $exercises = $query->paginate($request->get('per_page', 20));

        return response()->success($exercises, 'Exercises retrieved successfully');
    }

    /**
     * Create a new exercise
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'intensity' => 'required|in:낮음,보통,높음,매우 높음',
            'met_value' => 'required|numeric|min:0|max:30',
            'calories_per_hour_per_kg' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'video_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $exercise = Exercise::create($validator->validated());

        return response()->created(['exercise' => $exercise], 'Exercise created successfully');
    }

    /**
     * Get exercise details
     */
    public function show(Exercise $exercise): JsonResponse
    {
        return response()->success(['exercise' => $exercise], 'Exercise retrieved successfully');
    }

    /**
     * Update an exercise
     */
    public function update(Request $request, Exercise $exercise): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'category' => 'string|max:100',
            'intensity' => 'in:낮음,보통,높음,매우 높음',
            'met_value' => 'numeric|min:0|max:30',
            'calories_per_hour_per_kg' => 'numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'video_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $exercise->update($validator->validated());

        return response()->success(['exercise' => $exercise->fresh()], 'Exercise updated successfully');
    }

    /**
     * Delete an exercise
     */
    public function destroy(Exercise $exercise): JsonResponse
    {
        // Check if exercise is being used
        $usageCount = $exercise->exerciseLogs()->count() + $exercise->dailyExercisePlans()->count();

        if ($usageCount > 0) {
            return response()->error(
                "Cannot delete exercise. It's being used in {$usageCount} logs or plans.",
                null,
                409
            );
        }

        $exercise->delete();

        return response()->success(null, 'Exercise deleted successfully');
    }

    /**
     * Get exercise categories
     */
    public function categories(): JsonResponse
    {
        $categories = Exercise::distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return response()->success(['categories' => $categories], 'Categories retrieved successfully');
    }

    /**
     * Get intensity levels
     */
    public function intensities(): JsonResponse
    {
        $intensities = ['낮음', '보통', '높음', '매우 높음'];

        return response()->success(['intensities' => $intensities], 'Intensities retrieved successfully');
    }

    /**
     * Bulk import exercises
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exercises' => 'required|array|min:1|max:100',
            'exercises.*.name' => 'required|string|max:255',
            'exercises.*.category' => 'required|string|max:100',
            'exercises.*.intensity' => 'required|in:낮음,보통,높음,매우 높음',
            'exercises.*.met_value' => 'required|numeric|min:0',
            'exercises.*.calories_per_hour_per_kg' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $imported = 0;
        $errors = [];

        foreach ($request->exercises as $index => $exerciseData) {
            try {
                Exercise::create($exerciseData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$index}: " . $e->getMessage();
            }
        }

        return response()->success([
            'imported' => $imported,
            'errors' => $errors,
        ], "{$imported} exercises imported successfully");
    }
}
