<?php

namespace App\Http\Controllers\Api;

use App\Enums\SurveyStep;
use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SurveyController extends Controller
{
    public function __construct(
        private SurveyService $surveyService
    ) {
    }

    /**
     * 활성화된 설문 조회
     *
     * GET /api/surveys
     */
    public function index(): JsonResponse
    {
        $survey = $this->surveyService->getActiveSurvey();

        if (!$survey) {
            return response()->notFound('활성화된 설문이 없습니다.');
        }

        return response()->success([
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
                'total_questions' => $survey->total_questions,
            ],
        ]);
    }

    /**
     * 특정 단계의 질문 조회
     *
     * GET /api/surveys/{survey}/questions?step=1
     */
    public function getQuestions(Request $request, Survey $survey): JsonResponse
    {
        $validated = $request->validate([
            'step' => 'required|integer|min:1|max:5',
        ]);

        $step = SurveyStep::tryFrom($validated['step']);

        if (!$step) {
            return response()->validationError(
                ['step' => '유효하지 않은 단계입니다.'],
                '유효하지 않은 단계입니다.'
            );
        }

        $questions = $this->surveyService->getQuestionsByStep($survey, $step);

        return response()->success([
            'step' => $step->value,
            'step_name' => $step->displayName(),
            'description' => $step->description(),
            'questions' => $questions,
        ]);
    }

    /**
     * 답변 제출
     *
     * POST /api/surveys/{survey}/answers
     * Body: { "answers": { "1": "답변1", "2": "답변2" } }
     */
    public function submitAnswers(Request $request, Survey $survey): JsonResponse
    {
        $validated = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*' => 'required',
        ]);

        try {
            $result = $this->surveyService->submitAnswers(
                $request->user(),
                $survey,
                $validated['answers']
            );

            return response()->success($result, '답변이 저장되었습니다.');
        } catch (ValidationException $e) {
            return response()->validationError($e->errors(), $e->getMessage());
        }
    }

    /**
     * 사용자의 설문 응답 조회
     *
     * GET /api/surveys/{survey}/responses?step=1
     */
    public function getResponses(Request $request, Survey $survey): JsonResponse
    {
        $validated = $request->validate([
            'step' => 'nullable|integer|min:1|max:5',
        ]);

        $step = isset($validated['step']) ? (int) $validated['step'] : null;

        $responses = $this->surveyService->getUserResponses(
            $request->user(),
            $survey,
            $step
        );

        return response()->success([
            'responses' => $responses,
        ]);
    }

    /**
     * 사용자의 설문 진행률 조회
     *
     * GET /api/surveys/{survey}/progress
     */
    public function getProgress(Request $request, Survey $survey): JsonResponse
    {
        $progress = $this->surveyService->getUserProgress(
            $request->user(),
            $survey
        );

        return response()->success($progress);
    }

    /**
     * 설문 요약 정보 조회
     *
     * GET /api/surveys/{survey}/summary
     */
    public function getSummary(Request $request, Survey $survey): JsonResponse
    {
        $summary = $this->surveyService->getSurveySummary(
            $request->user(),
            $survey
        );

        return response()->success($summary);
    }
}
