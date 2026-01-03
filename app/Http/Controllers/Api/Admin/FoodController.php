<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    /**
     * Get paginated list of foods
     */
    public function index(Request $request): JsonResponse
    {
        $query = Food::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $foods = $query->paginate($request->get('per_page', 20));

        return response()->success($foods, 'Foods retrieved successfully');
    }

    /**
     * Create a new food
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'serving_size' => 'required|numeric|min:0',
            'calories' => 'required|numeric|min:0',
            'protein_g' => 'required|numeric|min:0',
            'carbs_g' => 'required|numeric|min:0',
            'fat_g' => 'required|numeric|min:0',
            'fiber_g' => 'nullable|numeric|min:0',
            'sodium_mg' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $food = Food::create($validator->validated());

        return response()->created(['food' => $food], 'Food created successfully');
    }

    /**
     * Get food details
     */
    public function show(Food $food): JsonResponse
    {
        return response()->success(['food' => $food], 'Food retrieved successfully');
    }

    /**
     * Update a food
     */
    public function update(Request $request, Food $food): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category' => 'string|max:100',
            'serving_size' => 'numeric|min:0',
            'calories' => 'numeric|min:0',
            'protein_g' => 'numeric|min:0',
            'carbs_g' => 'numeric|min:0',
            'fat_g' => 'numeric|min:0',
            'fiber_g' => 'nullable|numeric|min:0',
            'sodium_mg' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $food->update($validator->validated());

        return response()->success(['food' => $food->fresh()], 'Food updated successfully');
    }

    /**
     * Delete a food
     */
    public function destroy(Food $food): JsonResponse
    {
        // Check if food is being used in meal logs or plans
        $usageCount = $food->mealLogs()->count() + $food->mealPlanItems()->count();

        if ($usageCount > 0) {
            return response()->error(
                "Cannot delete food. It's being used in {$usageCount} meal logs or plans.",
                null,
                409
            );
        }

        $food->delete();

        return response()->success(null, 'Food deleted successfully');
    }

    /**
     * Get food categories
     */
    public function categories(): JsonResponse
    {
        $categories = Food::distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return response()->success(['categories' => $categories], 'Categories retrieved successfully');
    }

    /**
     * Bulk import foods
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'foods' => 'required|array|min:1|max:100',
            'foods.*.name' => 'required|string|max:255',
            'foods.*.category' => 'required|string|max:100',
            'foods.*.serving_size' => 'required|numeric|min:0',
            'foods.*.calories' => 'required|numeric|min:0',
            'foods.*.protein_g' => 'required|numeric|min:0',
            'foods.*.carbs_g' => 'required|numeric|min:0',
            'foods.*.fat_g' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $imported = 0;
        $errors = [];

        foreach ($request->foods as $index => $foodData) {
            try {
                Food::create($foodData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$index}: " . $e->getMessage();
            }
        }

        return response()->success([
            'imported' => $imported,
            'errors' => $errors,
        ], "{$imported} foods imported successfully");
    }
}
