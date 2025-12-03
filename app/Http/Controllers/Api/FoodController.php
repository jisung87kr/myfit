<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    /**
     * Display a listing of foods with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Food::query();

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
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
        $foods = $query->paginate($perPage);

        return response()->success('Foods retrieved successfully', $foods);
    }

    /**
     * Display the specified food
     */
    public function show(Food $food): JsonResponse
    {
        return response()->success('Food retrieved successfully', $food);
    }

    /**
     * Store a newly created food
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category' => 'required|in:곡류,단백질,채소,과일,유제품,견과류,음료,기타',
            'serving_size' => 'required|numeric|min:0.01|max:9999.99',
            'calories' => 'required|numeric|min:0|max:9999.99',
            'protein_g' => 'required|numeric|min:0|max:999.99',
            'carbs_g' => 'required|numeric|min:0|max:999.99',
            'fat_g' => 'required|numeric|min:0|max:999.99',
            'fiber_g' => 'nullable|numeric|min:0|max:999.99',
            'sodium_mg' => 'nullable|numeric|min:0|max:9999.99',
            'image_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $food = Food::create($validator->validated());

        return response()->success('Food created successfully', $food, 201);
    }

    /**
     * Update the specified food
     */
    public function update(Request $request, Food $food): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category' => 'sometimes|required|in:곡류,단백질,채소,과일,유제품,견과류,음료,기타',
            'serving_size' => 'sometimes|required|numeric|min:0.01|max:9999.99',
            'calories' => 'sometimes|required|numeric|min:0|max:9999.99',
            'protein_g' => 'sometimes|required|numeric|min:0|max:999.99',
            'carbs_g' => 'sometimes|required|numeric|min:0|max:999.99',
            'fat_g' => 'sometimes|required|numeric|min:0|max:999.99',
            'fiber_g' => 'nullable|numeric|min:0|max:999.99',
            'sodium_mg' => 'nullable|numeric|min:0|max:9999.99',
            'image_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', $validator->errors(), 422);
        }

        $food->update($validator->validated());

        return response()->success('Food updated successfully', $food);
    }

    /**
     * Remove the specified food
     */
    public function destroy(Food $food): JsonResponse
    {
        $food->delete();

        return response()->success('Food deleted successfully');
    }

    /**
     * Get available food categories
     */
    public function categories(): JsonResponse
    {
        $categories = ['곡류', '단백질', '채소', '과일', '유제품', '견과류', '음료', '기타'];

        return response()->success('Categories retrieved successfully', $categories);
    }
}
