<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveySubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    /**
     * Complete the onboarding survey
     */
    public function complete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'age' => 'required|integer|min:1|max:120',
            'gender' => 'required|string|in:male,female',
            'height_cm' => 'required|numeric|min:50|max:300',
            'current_weight_kg' => 'required|numeric|min:20|max:500',
            'goal_type' => 'required|string|in:weight_loss,muscle_gain,maintenance',
            'target_weight_kg' => 'required|numeric|min:20|max:500',
            'activity_level' => 'required|string|in:sedentary,lightly_active,moderately_active,very_active,extra_active',
            'diet_style' => 'required|string|in:balanced,low_carb,high_protein,vegetarian',
            'dietary_restrictions' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '입력값이 올바르지 않습니다.',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $survey = Survey::where('is_active', true)->first();

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => '활성화된 설문이 없습니다.',
            ], 400);
        }

        // Map the simple survey data to the expected format
        $surveyData = $this->mapSurveyData($request->all());

        // Create or update the survey submission
        $submission = SurveySubmission::updateOrCreate(
            [
                'user_id' => $user->id,
                'survey_id' => $survey->id,
            ],
            [
                'completion_data' => $surveyData,
                'submitted_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => '설문이 완료되었습니다.',
            'data' => [
                'submission_id' => $submission->id,
            ],
        ]);
    }

    /**
     * Map simple survey form data to the expected format for DietPlanService
     */
    private function mapSurveyData(array $data): array
    {
        // Map gender
        $gender = $data['gender'] === 'male' ? '남성' : '여성';

        // Map goal type
        $goalTypeMap = [
            'weight_loss' => '체중 감량',
            'muscle_gain' => '근육량 증가',
            'maintenance' => '체형 유지',
        ];

        // Map activity level
        $activityLevelMap = [
            'sedentary' => '매우 낮음',
            'lightly_active' => '낮음',
            'moderately_active' => '보통',
            'very_active' => '높음',
            'extra_active' => '매우 높음',
        ];

        // Map diet style to food preferences
        $dietStyleMap = [
            'balanced' => ['한식', '양식', '샐러드'],
            'low_carb' => ['육류', '해산물', '채소'],
            'high_protein' => ['육류', '해산물', '샐러드'],
            'vegetarian' => ['채소', '샐러드', '한식'],
        ];

        return [
            '성별' => $gender,
            '나이' => $data['age'],
            '키 (cm)' => $data['height_cm'],
            '현재 체중 (kg)' => $data['current_weight_kg'],
            '목표 체중 (kg)' => $data['target_weight_kg'],
            '주요 목표' => $goalTypeMap[$data['goal_type']] ?? '체중 감량',
            '일일 활동량' => $activityLevelMap[$data['activity_level']] ?? '보통',
            '희망 감량 기간' => '8주',
            '운동 경험' => '초보',
            '하루 식사 횟수' => '3회',
            '선호하는 음식 종류' => $dietStyleMap[$data['diet_style']] ?? ['한식', '양식'],
            '싫어하는 음식 재료' => ['없음'],
            '식이 제한' => $data['dietary_restrictions'] ? [$data['dietary_restrictions']] : ['없음'],
            '조리 가능 여부' => '간단한 조리만 가능',
        ];
    }
}
