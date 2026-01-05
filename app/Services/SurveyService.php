<?php

namespace App\Services;

use App\Enums\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveySubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyService
{
    /**
     * 활성화된 설문 조회
     */
    public function getActiveSurvey(): ?Survey
    {
        return Survey::active()
            ->with('questions')
            ->first();
    }

    /**
     * 특정 단계의 질문들 조회
     */
    public function getQuestionsByStep(Survey $survey, SurveyStep|int $step): array
    {
        $stepValue = $step instanceof SurveyStep ? $step->value : $step;

        $questions = SurveyQuestion::where('survey_id', $survey->id)
            ->where('step', $stepValue)
            ->orderBy('order')
            ->get();

        return $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type->value,
                'options' => $question->options,
                'is_required' => $question->is_required,
                'order' => $question->order,
            ];
        })->toArray();
    }

    /**
     * 설문 제출 (completion_data에 모든 답변 저장)
     *
     * @throws ValidationException
     */
    public function submitSurvey(User $user, Survey $survey, array $answers = []): array
    {
        DB::beginTransaction();

        try {
            // 이미 제출했는지 확인
            $existingSubmission = SurveySubmission::where('user_id', $user->id)
                ->where('survey_id', $survey->id)
                ->first();

            if ($existingSubmission) {
                // 기존 제출 업데이트
                $existingSubmission->update([
                    'completion_data' => $answers,
                    'submitted_at' => now(),
                ]);

                DB::commit();

                return [
                    'submission_id' => $existingSubmission->id,
                    'submitted_at' => $existingSubmission->submitted_at->toISOString(),
                    'message' => '설문이 업데이트되었습니다.',
                ];
            }

            // 새 제출 생성
            $submission = SurveySubmission::create([
                'user_id' => $user->id,
                'survey_id' => $survey->id,
                'completion_data' => $answers,
                'submitted_at' => now(),
            ]);

            DB::commit();

            return [
                'submission_id' => $submission->id,
                'submitted_at' => $submission->submitted_at->toISOString(),
                'message' => '설문이 성공적으로 제출되었습니다.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 제출 여부 확인
     */
    public function hasSubmitted(User $user, Survey $survey): bool
    {
        return SurveySubmission::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->exists();
    }

    /**
     * 제출 정보 조회
     */
    public function getSubmission(User $user, Survey $survey): ?array
    {
        $submission = SurveySubmission::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->first();

        if (!$submission) {
            return null;
        }

        return [
            'id' => $submission->id,
            'submitted_at' => $submission->submitted_at->toISOString(),
            'completion_data' => $submission->completion_data,
        ];
    }

    /**
     * 사용자의 설문 응답 조회 (completion_data에서)
     */
    public function getUserResponses(User $user, Survey $survey): array
    {
        $submission = SurveySubmission::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->first();

        if (!$submission) {
            return [];
        }

        return $submission->completion_data ?? [];
    }

    /**
     * 설문 상태 조회
     */
    public function getSurveyStatus(User $user, Survey $survey): array
    {
        $submission = SurveySubmission::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->first();

        if (!$submission) {
            return [
                'status' => 'not_started',
                'is_completed' => false,
            ];
        }

        return [
            'status' => 'completed',
            'is_completed' => true,
            'submitted_at' => $submission->submitted_at->toISOString(),
        ];
    }

    /**
     * 설문 요약 정보 조회
     */
    public function getSurveySummary(User $user, Survey $survey): array
    {
        $submission = $this->getSubmission($user, $survey);
        $status = $this->getSurveyStatus($user, $survey);

        return [
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
            ],
            'status' => $status,
            'submission' => $submission,
        ];
    }

    /**
     * 설문 응답 초기화 (제출 삭제)
     */
    public function resetSurvey(User $user, Survey $survey): bool
    {
        $deleted = SurveySubmission::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->delete();

        return $deleted > 0;
    }
}
