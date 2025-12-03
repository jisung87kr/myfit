<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExerciseController extends Controller
{
    /**
     * Display a listing of exercises with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Exercise::query();

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by intensity
        if ($request->has('intensity')) {
            $query->byIntensity($request->intensity);
        }

        // Search by name
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $exercises = $query->paginate($perPage);

        return response()->success('Exercises retrieved successfully', $exercises);
    }

    /**
     * Display the specified exercise
     */
    public function show(Exercise $exercise): JsonResponse
    {
        return response()->success('Exercise retrieved successfully', $exercise);
    }

    /**
     * Store a newly created exercise
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:유산소,근력,스트레칭,스포츠',
            'intensity' => 'required|in:낮음,보통,높음',
            'met_value' => 'required|numeric|min:0.01|max:99.99',
            'calories_per_hour_per_kg' => 'required|numeric|min:0.01|max:9999.99',
            'description' => 'nullable|string|max:1000',
            'video_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $exercise = Exercise::create($validator->validated());

        return response()->success('Exercise created successfully', $exercise, 201);
    }

    /**
     * Update the specified exercise
     */
    public function update(Request $request, Exercise $exercise): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|in:유산소,근력,스트레칭,스포츠',
            'intensity' => 'sometimes|required|in:낮음,보통,높음',
            'met_value' => 'sometimes|required|numeric|min:0.01|max:99.99',
            'calories_per_hour_per_kg' => 'sometimes|required|numeric|min:0.01|max:9999.99',
            'description' => 'nullable|string|max:1000',
            'video_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $exercise->update($validator->validated());

        return response()->success('Exercise updated successfully', $exercise);
    }

    /**
     * Remove the specified exercise
     */
    public function destroy(Exercise $exercise): JsonResponse
    {
        $exercise->delete();

        return response()->success('Exercise deleted successfully');
    }

    /**
     * Calculate calories burned for a specific exercise
     */
    public function calculateCalories(Request $request, Exercise $exercise): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'weight_kg' => 'required|numeric|min:20|max:300',
            'duration_minutes' => 'required|numeric|min:1|max:1440',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $weightKg = $request->weight_kg;
        $hours = $request->duration_minutes / 60;

        $caloriesBurned = $exercise->calculateCalories($weightKg, $hours);

        return response()->success('Calories calculated successfully', [
            'exercise' => $exercise->name,
            'weight_kg' => $weightKg,
            'duration_minutes' => $request->duration_minutes,
            'calories_burned' => $caloriesBurned,
            'met_value' => $exercise->met_value,
        ]);
    }

    /**
     * Get available exercise categories
     */
    public function categories(): JsonResponse
    {
        $categories = ['유산소', '근력', '스트레칭', '스포츠'];

        return response()->success('Categories retrieved successfully', $categories);
    }

    /**
     * Get available intensity levels
     */
    public function intensities(): JsonResponse
    {
        $intensities = ['낮음', '보통', '높음'];

        return response()->success('Intensities retrieved successfully', $intensities);
    }
}
