<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalorieCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CalculationController extends Controller
{
    public function __construct(
        private CalorieCalculationService $calculationService
    ) {}

    /**
     * BMR 계산
     *
     * POST /api/calculations/bmr
     */
    public function calculateBMR(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gender' => 'required|in:male,female,남성,여성',
            'weight' => 'required|numeric|min:30|max:300',
            'height' => 'required|numeric|min:100|max:250',
            'age' => 'required|integer|min:15|max:100',
        ]);

        $bmr = $this->calculationService->calculateBMR(
            $validated['gender'],
            $validated['weight'],
            $validated['height'],
            $validated['age']
        );

        return response()->success([
            'bmr' => $bmr,
            'unit' => 'kcal/day',
            'formula' => 'Harris-Benedict',
        ], 'BMR이 계산되었습니다.');
    }

    /**
     * TDEE 계산
     *
     * POST /api/calculations/tdee
     */
    public function calculateTDEE(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bmr' => 'required|numeric|min:500|max:5000',
            'activity_level' => 'required|in:sedentary,lightly_active,moderately_active,very_active,extra_active',
        ]);

        $tdee = $this->calculationService->calculateTDEE(
            $validated['bmr'],
            $validated['activity_level']
        );

        return response()->success([
            'tdee' => $tdee,
            'bmr' => $validated['bmr'],
            'activity_level' => $validated['activity_level'],
            'unit' => 'kcal/day',
        ], 'TDEE가 계산되었습니다.');
    }

    /**
     * 목표 칼로리 계산
     *
     * POST /api/calculations/target-calories
     */
    public function calculateTargetCalories(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tdee' => 'required|numeric|min:500|max:5000',
            'goal' => 'required|in:lose_0.5kg,lose_1kg,maintain,gain_0.5kg,gain_1kg',
        ]);

        $targetCalories = $this->calculationService->calculateTargetCalories(
            $validated['tdee'],
            $validated['goal']
        );

        $macros = $this->calculationService->calculateMacros($targetCalories);

        return response()->success([
            'target_calories' => $targetCalories,
            'tdee' => $validated['tdee'],
            'goal' => $validated['goal'],
            'macros' => [
                'protein_g' => $macros['protein_g'],
                'carbs_g' => $macros['carbs_g'],
                'fat_g' => $macros['fat_g'],
            ],
            'unit' => 'kcal/day',
        ], '목표 칼로리가 계산되었습니다.');
    }

    /**
     * 사용자의 전체 계산 수행 (설문 기반)
     *
     * POST /api/calculations/calculate
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            $calculation = $this->calculationService->calculateForUser($request->user());

            return response()->success([
                'id' => $calculation->id,
                'bmr' => $calculation->bmr,
                'tdee' => $calculation->tdee,
                'target_calories' => $calculation->target_calories,
                'macros' => [
                    'protein_g' => $calculation->target_protein_g,
                    'carbs_g' => $calculation->target_carbs_g,
                    'fat_g' => $calculation->target_fat_g,
                ],
                'calculated_at' => $calculation->calculated_at->toISOString(),
            ], '칼로리 계산이 완료되었습니다.');
        } catch (ValidationException $e) {
            return response()->validationError(
                $e->errors(),
                $e->getMessage()
            );
        }
    }

    /**
     * 사용자의 최신 계산 결과 조회
     *
     * GET /api/calculations/latest
     */
    public function getLatest(Request $request): JsonResponse
    {
        $calculation = $this->calculationService->getLatestCalculation($request->user());

        if (!$calculation) {
            return response()->notFound('계산 기록이 없습니다. 먼저 설문을 완료하고 계산을 수행해주세요.');
        }

        return response()->success([
            'id' => $calculation->id,
            'bmr' => $calculation->bmr,
            'tdee' => $calculation->tdee,
            'target_calories' => $calculation->target_calories,
            'macros' => [
                'protein_g' => $calculation->target_protein_g,
                'carbs_g' => $calculation->target_carbs_g,
                'fat_g' => $calculation->target_fat_g,
            ],
            'calculated_at' => $calculation->calculated_at->toISOString(),
        ]);
    }
}
