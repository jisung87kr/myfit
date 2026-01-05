<?php

namespace App\Services;

use App\Models\SurveySubmission;
use App\Models\User;
use App\Models\UserCalculation;
use Illuminate\Validation\ValidationException;

class CalorieCalculationService
{
    /**
     * 활동 계수 매핑
     */
    private const ACTIVITY_FACTORS = [
        'sedentary' => 1.2,      // 좌식 (거의 운동 안 함)
        'lightly_active' => 1.375, // 가벼운 활동 (주 1-3회)
        'moderately_active' => 1.55, // 보통 활동 (주 3-5회)
        'very_active' => 1.725,   // 활동적 (주 6-7회)
        'extra_active' => 1.9,    // 매우 활동적 (하루 2회 이상)
    ];

    /**
     * 목표별 칼로리 조정
     */
    private const GOAL_ADJUSTMENTS = [
        'lose_0.5kg' => -500,     // 주 0.5kg 감량
        'lose_1kg' => -1000,      // 주 1kg 감량
        'maintain' => 0,          // 체중 유지
        'gain_0.5kg' => 300,      // 주 0.5kg 증량
        'gain_1kg' => 500,        // 주 1kg 증량
    ];

    /**
     * 최소 칼로리 (안전 기준)
     */
    private const MIN_CALORIES = 1200;

    /**
     * 영양소 비율 (탄수화물, 단백질, 지방)
     */
    private const MACRO_RATIOS = [
        'carbs' => 0.40,    // 40%
        'protein' => 0.30,  // 30%
        'fat' => 0.30,      // 30%
    ];

    /**
     * BMR 계산 (Harris-Benedict 공식)
     *
     * @param string $gender 'male' or 'female'
     * @param float $weight 체중 (kg)
     * @param float $height 신장 (cm)
     * @param int $age 나이
     * @return float BMR (kcal/day)
     */
    public function calculateBMR(string $gender, float $weight, float $height, int $age): float
    {
        if ($gender === 'male' || $gender === '남성') {
            // 남성: 88.362 + (13.397 × 체중kg) + (4.799 × 키cm) - (5.677 × 나이)
            $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
        } else {
            // 여성: 447.593 + (9.247 × 체중kg) + (3.098 × 키cm) - (4.330 × 나이)
            $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
        }

        return round($bmr, 2);
    }

    /**
     * TDEE 계산 (Total Daily Energy Expenditure)
     *
     * @param float $bmr BMR 값
     * @param string $activityLevel 활동 수준
     * @return float TDEE (kcal/day)
     */
    public function calculateTDEE(float $bmr, string $activityLevel): float
    {
        $activityFactor = self::ACTIVITY_FACTORS[$activityLevel] ?? 1.2;
        return round($bmr * $activityFactor, 2);
    }

    /**
     * 목표 칼로리 계산
     *
     * @param float $tdee TDEE 값
     * @param string $goal 목표 (lose_0.5kg, lose_1kg, maintain, gain_0.5kg, gain_1kg)
     * @return float 목표 칼로리 (kcal/day)
     */
    public function calculateTargetCalories(float $tdee, string $goal): float
    {
        $adjustment = self::GOAL_ADJUSTMENTS[$goal] ?? 0;
        $targetCalories = $tdee + $adjustment;

        // 최소 칼로리 보장 (안전 기준)
        if ($targetCalories < self::MIN_CALORIES) {
            $targetCalories = self::MIN_CALORIES;
        }

        return round($targetCalories, 2);
    }

    /**
     * 영양소 목표량 계산 (탄수화물, 단백질, 지방)
     *
     * @param float $targetCalories 목표 칼로리
     * @return array ['protein_g' => float, 'carbs_g' => float, 'fat_g' => float]
     */
    public function calculateMacros(float $targetCalories): array
    {
        // 칼로리 당 그램 환산
        // 탄수화물: 4kcal/g
        // 단백질: 4kcal/g
        // 지방: 9kcal/g

        $carbsCalories = $targetCalories * self::MACRO_RATIOS['carbs'];
        $proteinCalories = $targetCalories * self::MACRO_RATIOS['protein'];
        $fatCalories = $targetCalories * self::MACRO_RATIOS['fat'];

        return [
            'carbs_g' => round($carbsCalories / 4, 2),
            'protein_g' => round($proteinCalories / 4, 2),
            'fat_g' => round($fatCalories / 9, 2),
        ];
    }

    /**
     * 사용자의 설문 데이터를 기반으로 전체 계산 수행
     *
     * @param User $user
     * @return UserCalculation
     * @throws ValidationException
     */
    public function calculateForUser(User $user): UserCalculation
    {
        // 1. 설문 데이터 조회
        $surveyData = $this->getUserSurveyData($user);

        // 2. 필수 데이터 검증
        $this->validateSurveyData($surveyData);

        // 3. BMR 계산
        $bmr = $this->calculateBMR(
            $surveyData['gender'],
            $surveyData['weight'],
            $surveyData['height'],
            $surveyData['age']
        );

        // 4. TDEE 계산
        $tdee = $this->calculateTDEE($bmr, $surveyData['activity_level']);

        // 5. 목표 칼로리 계산
        $targetCalories = $this->calculateTargetCalories($tdee, $surveyData['goal']);

        // 6. 영양소 목표량 계산
        $macros = $this->calculateMacros($targetCalories);

        // 7. 데이터베이스에 저장
        $calculation = UserCalculation::create([
            'user_id' => $user->id,
            'bmr' => $bmr,
            'tdee' => $tdee,
            'target_calories' => $targetCalories,
            'target_protein_g' => $macros['protein_g'],
            'target_carbs_g' => $macros['carbs_g'],
            'target_fat_g' => $macros['fat_g'],
            'calculated_at' => now(),
        ]);

        return $calculation;
    }

    /**
     * 사용자의 최신 계산 결과 조회
     *
     * @param User $user
     * @return UserCalculation|null
     */
    public function getLatestCalculation(User $user): ?UserCalculation
    {
        return UserCalculation::where('user_id', $user->id)
            ->latest('calculated_at')
            ->first();
    }

    /**
     * 사용자의 설문 응답에서 필요한 데이터 추출
     *
     * @param User $user
     * @return array
     * @throws ValidationException
     */
    private function getUserSurveyData(User $user): array
    {
        $submission = SurveySubmission::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$submission) {
            return [];
        }

        $responses = $submission->completion_data ?? [];
        $data = [];

        // 성별
        if (isset($responses['성별'])) {
            $data['gender'] = $responses['성별'];
        }

        // 나이
        if (isset($responses['나이'])) {
            $data['age'] = (int) $responses['나이'];
        }

        // 체중
        if (isset($responses['현재 체중 (kg)'])) {
            $data['weight'] = (float) $responses['현재 체중 (kg)'];
        }

        // 신장
        if (isset($responses['키 (cm)'])) {
            $data['height'] = (float) $responses['키 (cm)'];
        }

        // 활동량
        if (isset($responses['일일 활동량'])) {
            $data['activity_level'] = $this->mapActivityLevel($responses['일일 활동량']);
        }

        // 목표
        if (isset($responses['주요 목표'])) {
            $data['goal'] = $this->mapGoal($responses['주요 목표']);
        }

        return $data;
    }

    /**
     * 활동량 매핑
     */
    private function mapActivityLevel(string $answer): string
    {
        $mapping = [
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

        return $mapping[$answer] ?? 'sedentary';
    }

    /**
     * 목표 매핑
     */
    private function mapGoal(string $answer): string
    {
        if (str_contains($answer, '체중 감량') || str_contains($answer, '다이어트')) {
            return 'lose_0.5kg'; // 기본 안전한 감량
        }

        if (str_contains($answer, '체중 유지')) {
            return 'maintain';
        }

        if (str_contains($answer, '근육 증가') || str_contains($answer, '체중 증가')) {
            return 'gain_0.5kg';
        }

        return 'maintain';
    }

    /**
     * 설문 데이터 검증
     *
     * @throws ValidationException
     */
    private function validateSurveyData(array $data): void
    {
        $required = ['gender', 'age', 'weight', 'height', 'activity_level', 'goal'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw ValidationException::withMessages([
                    $field => "설문에서 {$field} 정보를 찾을 수 없습니다. 설문을 완료해주세요.",
                ]);
            }
        }

        // 데이터 범위 검증
        if ($data['age'] < 15 || $data['age'] > 100) {
            throw ValidationException::withMessages([
                'age' => '나이는 15-100 사이여야 합니다.',
            ]);
        }

        if ($data['weight'] < 30 || $data['weight'] > 300) {
            throw ValidationException::withMessages([
                'weight' => '체중은 30-300kg 사이여야 합니다.',
            ]);
        }

        if ($data['height'] < 100 || $data['height'] > 250) {
            throw ValidationException::withMessages([
                'height' => '신장은 100-250cm 사이여야 합니다.',
            ]);
        }
    }
}
