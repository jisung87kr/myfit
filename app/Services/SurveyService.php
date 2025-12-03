<?php

namespace App\Services;

use App\Enums\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveySubmission;
use App\Models\User;
use App\Models\UserSurveyResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
     * 사용자 답변 제출
     *
     * @param User $user
     * @param Survey $survey
     * @param array $answers [question_id => answer] 형식
     * @throws ValidationException
     */
    public function submitAnswers(User $user, Survey $survey, array $answers): array
    {
        DB::beginTransaction();

        try {
            $savedAnswers = [];

            foreach ($answers as $questionId => $answer) {
                $question = SurveyQuestion::findOrFail($questionId);

                // 질문이 해당 설문에 속하는지 확인
                if ($question->survey_id !== $survey->id) {
                    throw ValidationException::withMessages([
                        'question_id' => "질문 ID {$questionId}는 이 설문에 속하지 않습니다.",
                    ]);
                }

                // 답변 검증
                if (!$question->validateAnswer($answer)) {
                    throw ValidationException::withMessages([
                        "question_{$questionId}" => "질문에 대한 답변이 유효하지 않습니다.",
                    ]);
                }

                // 답변 저장 또는 업데이트
                $response = UserSurveyResponse::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'survey_question_id' => $questionId,
                    ],
                    [
                        'survey_id' => $survey->id,
                        'answer' => is_array($answer) ? $answer : ['value' => $answer],
                        'answered_at' => now(),
                    ]
                );

                $savedAnswers[] = [
                    'question_id' => $questionId,
                    'answer' => $response->answer,
                    'answered_at' => $response->answered_at,
                ];
            }

            DB::commit();

            return [
                'saved_count' => count($savedAnswers),
                'answers' => $savedAnswers,
                'progress' => $this->getUserProgress($user, $survey),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 사용자의 설문 응답 조회
     */
    public function getUserResponses(User $user, Survey $survey, ?int $step = null): array
    {
        $query = UserSurveyResponse::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->with('question');

        if ($step !== null) {
            $query->whereHas('question', function ($q) use ($step) {
                $q->where('step', $step);
            });
        }

        $responses = $query->get();

        return $responses->map(function ($response) {
            return [
                'question_id' => $response->survey_question_id,
                'question_text' => $response->question->question_text,
                'question_type' => $response->question->question_type->value,
                'step' => $response->question->step->value,
                'answer' => $response->answer,
                'answered_at' => $response->answered_at->toISOString(),
            ];
        })->toArray();
    }

    /**
     * 사용자의 설문 진행률 조회
     */
    public function getUserProgress(User $user, Survey $survey): array
    {
        $totalQuestions = $survey->questions()->count();
        $answeredQuestions = UserSurveyResponse::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->distinct('survey_question_id')
            ->count('survey_question_id');

        $percentage = $totalQuestions > 0
            ? (int) (($answeredQuestions / $totalQuestions) * 100)
            : 0;

        // 단계별 진행률
        $stepProgress = [];
        foreach (SurveyStep::all() as $step) {
            $stepQuestions = $survey->questions()->where('step', $step->value)->count();
            $stepAnswered = UserSurveyResponse::where('user_id', $user->id)
                ->where('survey_id', $survey->id)
                ->whereHas('question', function ($q) use ($step) {
                    $q->where('step', $step->value);
                })
                ->count();

            $stepProgress[] = [
                'step' => $step->value,
                'step_name' => $step->displayName(),
                'total' => $stepQuestions,
                'answered' => $stepAnswered,
                'is_complete' => $stepQuestions > 0 && $stepAnswered === $stepQuestions,
            ];
        }

        return [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'percentage' => $percentage,
            'is_complete' => $totalQuestions > 0 && $answeredQuestions === $totalQuestions,
            'step_progress' => $stepProgress,
        ];
    }

    /**
     * 다음 단계 조회
     */
    public function getNextIncompleteStep(User $user, Survey $survey): ?array
    {
        foreach (SurveyStep::all() as $step) {
            $stepQuestions = $survey->questions()->where('step', $step->value)->count();

            if ($stepQuestions === 0) {
                continue;
            }

            $stepAnswered = UserSurveyResponse::where('user_id', $user->id)
                ->where('survey_id', $survey->id)
                ->whereHas('question', function ($q) use ($step) {
                    $q->where('step', $step->value);
                })
                ->count();

            if ($stepAnswered < $stepQuestions) {
                return [
                    'step' => $step->value,
                    'step_name' => $step->displayName(),
                    'description' => $step->description(),
                ];
            }
        }

        return null;
    }

    /**
     * 설문 완료 확인
     */
    public function isSurveyComplete(User $user, Survey $survey): bool
    {
        $progress = $this->getUserProgress($user, $survey);
        return $progress['is_complete'];
    }

    /**
     * 사용자의 설문 요약 정보 조회
     */
    public function getSurveySummary(User $user, Survey $survey): array
    {
        $responses = $this->getUserResponses($user, $survey);
        $progress = $this->getUserProgress($user, $survey);
        $nextStep = $this->getNextIncompleteStep($user, $survey);

        return [
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
            ],
            'progress' => $progress,
            'next_step' => $nextStep,
            'total_responses' => count($responses),
        ];
    }

    /**
     * 특정 질문의 답변 삭제
     */
    public function deleteAnswer(User $user, Survey $survey, int $questionId): bool
    {
        $question = SurveyQuestion::where('id', $questionId)
            ->where('survey_id', $survey->id)
            ->firstOrFail();

        $deleted = UserSurveyResponse::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->where('survey_question_id', $questionId)
            ->delete();

        return $deleted > 0;
    }

    /**
     * 설문 전체 응답 초기화
     */
    public function resetSurvey(User $user, Survey $survey): int
    {
        return UserSurveyResponse::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->delete();
    }

    /**
     * 설문 상태 조회
     */
    public function getSurveyStatus(User $user, Survey $survey): array
    {
        $totalQuestions = $survey->questions()->count();
        $answeredQuestions = UserSurveyResponse::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->distinct('survey_question_id')
            ->count('survey_question_id');

        $status = 'not_started';
        if ($answeredQuestions > 0) {
            $status = $answeredQuestions === $totalQuestions ? 'completed' : 'in_progress';
        }

        return [
            'status' => $status,
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'percentage' => $totalQuestions > 0 ? (int) (($answeredQuestions / $totalQuestions) * 100) : 0,
        ];
    }

    /**
     * 특정 단계의 응답 삭제
     */
    public function deleteStepResponses(User $user, Survey $survey, SurveyStep $step): int
    {
        return UserSurveyResponse::where('user_id', $user->id)
            ->where('survey_id', $survey->id)
            ->whereHas('question', function ($q) use ($step) {
                $q->where('step', $step->value);
            })
            ->delete();
    }

    /**
     * 설문 제출 (최종 완료)
     *
     * @throws ValidationException
     */
    public function submitSurvey(User $user, Survey $survey): array
    {
        DB::beginTransaction();

        try {
            // 1. 이미 제출했는지 확인
            $existingSubmission = SurveySubmission::where('user_id', $user->id)
                ->where('survey_id', $survey->id)
                ->first();

            if ($existingSubmission) {
                throw ValidationException::withMessages([
                    'survey' => '이미 제출한 설문입니다.',
                ]);
            }

            // 2. 모든 필수 질문에 답변했는지 확인
            $requiredQuestions = $survey->questions()
                ->where('is_required', true)
                ->pluck('id');

            $answeredRequiredQuestions = UserSurveyResponse::where('user_id', $user->id)
                ->where('survey_id', $survey->id)
                ->whereIn('survey_question_id', $requiredQuestions)
                ->pluck('survey_question_id');

            $unansweredQuestions = $requiredQuestions->diff($answeredRequiredQuestions);

            if ($unansweredQuestions->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'survey' => '모든 필수 질문에 답변해주세요.',
                    'unanswered_questions' => $unansweredQuestions->toArray(),
                ]);
            }

            // 3. 모든 응답 데이터 수집
            $responses = $this->getUserResponses($user, $survey);

            // 4. 제출 기록 생성
            $submission = SurveySubmission::create([
                'user_id' => $user->id,
                'survey_id' => $survey->id,
                'completion_data' => [
                    'responses' => $responses,
                    'total_questions' => $survey->questions()->count(),
                    'answered_questions' => count($responses),
                    'submitted_by' => $user->name,
                    'submitted_email' => $user->email,
                ],
                'submitted_at' => now(),
            ]);

            DB::commit();

            return [
                'submission_id' => $submission->id,
                'submitted_at' => $submission->submitted_at->toISOString(),
                'total_responses' => count($responses),
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
}
