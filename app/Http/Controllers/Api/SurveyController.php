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
     * 설문 제출 (모든 답변을 한 번에 제출)
     *
     * POST /api/surveys/{survey}/submit
     * Body: { "answers": { "질문텍스트": "답변", ... } }
     */
    public function submit(Request $request, Survey $survey): JsonResponse
    {
        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        try {
            $result = $this->surveyService->submitSurvey(
                $request->user(),
                $survey,
                $validated['answers']
            );

            return response()->success($result, $result['message']);
        } catch (ValidationException $e) {
            return response()->validationError(
                $e->errors(),
                $e->getMessage()
            );
        }
    }

    /**
     * 사용자의 설문 응답 조회
     *
     * GET /api/surveys/{survey}/responses
     */
    public function getResponses(Request $request, Survey $survey): JsonResponse
    {
        $responses = $this->surveyService->getUserResponses(
            $request->user(),
            $survey
        );

        return response()->success([
            'responses' => $responses,
        ]);
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

    /**
     * 설문 전체 응답 초기화
     *
     * DELETE /api/surveys/{survey}/reset
     */
    public function resetSurvey(Request $request, Survey $survey): JsonResponse
    {
        $deleted = $this->surveyService->resetSurvey(
            $request->user(),
            $survey
        );

        return response()->success([
            'deleted' => $deleted,
        ], $deleted ? '설문이 초기화되었습니다.' : '삭제할 설문이 없습니다.');
    }

    /**
     * 설문 상태 조회
     *
     * GET /api/surveys/{survey}/status
     */
    public function getStatus(Request $request, Survey $survey): JsonResponse
    {
        $status = $this->surveyService->getSurveyStatus(
            $request->user(),
            $survey
        );

        return response()->success($status);
    }

    /**
     * 제출 여부 확인
     *
     * GET /api/surveys/{survey}/submission
     */
    public function getSubmission(Request $request, Survey $survey): JsonResponse
    {
        $submission = $this->surveyService->getSubmission(
            $request->user(),
            $survey
        );

        if (!$submission) {
            return response()->notFound('제출 기록이 없습니다.');
        }

        return response()->success($submission);
    }
}
