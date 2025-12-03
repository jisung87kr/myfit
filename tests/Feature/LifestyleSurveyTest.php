<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Enums\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use App\Models\UserSurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifestyleSurveyTest extends TestCase
{
    use RefreshDatabase;

    private Survey $survey;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->survey = Survey::factory()->create(['is_active' => true]);

        // Create lifestyle questions matching the seeder
        $this->createLifestyleQuestions();
    }

    private function createLifestyleQuestions(): void
    {
        $questions = [
            [
                'question_text' => '평균 수면 시간은 얼마나 되나요?',
                'question_type' => QuestionType::SELECT,
                'options' => ['5시간 미만', '5-6시간', '6-7시간', '7-8시간', '8시간 이상'],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '하루 평균 식사 횟수는?',
                'question_type' => QuestionType::SELECT,
                'options' => ['1회', '2회', '3회', '4회 이상', '불규칙'],
                'is_required' => true,
                'order' => 2,
            ],
            [
                'question_text' => '주로 식사하는 시간대를 선택해주세요',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => [
                    '아침 (06:00-09:00)',
                    '오전 간식 (09:00-12:00)',
                    '점심 (12:00-14:00)',
                    '오후 간식 (14:00-18:00)',
                    '저녁 (18:00-21:00)',
                    '야식 (21:00 이후)',
                ],
                'is_required' => true,
                'order' => 3,
            ],
            [
                'question_text' => '야식(밤 9시 이후)을 얼마나 자주 먹나요?',
                'question_type' => QuestionType::SELECT,
                'options' => ['거의 안 먹음', '주 1-2회', '주 3-4회', '주 5-6회', '거의 매일'],
                'is_required' => true,
                'order' => 4,
            ],
            [
                'question_text' => '현재 직업/활동 유형은?',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '사무직 (주로 앉아서 근무)',
                    '서비스직 (주로 서서 근무)',
                    '육체 노동직',
                    '학생',
                    '주부',
                    '프리랜서/재택근무',
                    '무직/구직 중',
                ],
                'is_required' => true,
                'order' => 5,
            ],
            [
                'question_text' => '평소 스트레스 수준은?',
                'question_type' => QuestionType::SELECT,
                'options' => ['매우 낮음', '낮음', '보통', '높음', '매우 높음'],
                'is_required' => true,
                'order' => 6,
            ],
            [
                'question_text' => '하루 물 섭취량은 얼마나 되나요?',
                'question_type' => QuestionType::SELECT,
                'options' => ['500ml 미만', '500ml-1L', '1L-1.5L', '1.5L-2L', '2L 이상'],
                'is_required' => false,
                'order' => 7,
            ],
            [
                'question_text' => '음주 빈도는?',
                'question_type' => QuestionType::SELECT,
                'options' => ['거의 안 함', '월 1-2회', '주 1-2회', '주 3-4회', '거의 매일'],
                'is_required' => false,
                'order' => 8,
            ],
            [
                'question_text' => '흡연 여부는?',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '비흡연',
                    '과거 흡연 (현재 금연)',
                    '하루 반 갑 미만',
                    '하루 반 갑-1갑',
                    '하루 1갑 이상',
                ],
                'is_required' => false,
                'order' => 9,
            ],
        ];

        foreach ($questions as $questionData) {
            SurveyQuestion::create([
                'survey_id' => $this->survey->id,
                'step' => SurveyStep::LIFESTYLE,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }
    }

    /**
     * Test getting lifestyle questions
     */
    public function test_can_get_lifestyle_questions(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/questions?step=3");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'step' => 3,
                    'step_name' => '생활 패턴',
                ],
            ])
            ->assertJsonCount(9, 'data.questions');
    }

    /**
     * Test submitting complete lifestyle answers
     */
    public function test_can_submit_complete_lifestyle(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::LIFESTYLE)
            ->orderBy('order')
            ->get();

        $answers = [
            $questions[0]->id => '7-8시간',                              // 수면 시간
            $questions[1]->id => '3회',                                  // 식사 횟수
            $questions[2]->id => ['아침 (06:00-09:00)', '점심 (12:00-14:00)', '저녁 (18:00-21:00)'], // 식사 시간대
            $questions[3]->id => '거의 안 먹음',                         // 야식 빈도
            $questions[4]->id => '사무직 (주로 앉아서 근무)',            // 직업
            $questions[5]->id => '보통',                                 // 스트레스
            $questions[6]->id => '2L 이상',                              // 물 섭취
            $questions[7]->id => '거의 안 함',                           // 음주
            $questions[8]->id => '비흡연',                               // 흡연
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '답변이 저장되었습니다.',
                'data' => [
                    'saved_count' => 9,
                ],
            ]);
    }

    /**
     * Test submitting only required lifestyle fields
     */
    public function test_can_submit_only_required_lifestyle(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::LIFESTYLE)
            ->where('is_required', true)
            ->orderBy('order')
            ->get();

        $answers = [
            $questions[0]->id => '6-7시간',
            $questions[1]->id => '3회',
            $questions[2]->id => ['아침 (06:00-09:00)', '점심 (12:00-14:00)'],
            $questions[3]->id => '주 1-2회',
            $questions[4]->id => '학생',
            $questions[5]->id => '낮음',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'saved_count' => 6,
                ],
            ]);
    }

    /**
     * Test multi-select for meal times
     */
    public function test_can_submit_multiple_meal_times(): void
    {
        $mealTimeQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '주로 식사하는 시간대를 선택해주세요')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $mealTimeQuestion->id => ['아침 (06:00-09:00)', '점심 (12:00-14:00)', '저녁 (18:00-21:00)'],
                ],
            ]);

        $response->assertStatus(200);

        $savedResponse = UserSurveyResponse::where('user_id', $this->user->id)
            ->where('survey_question_id', $mealTimeQuestion->id)
            ->first();

        $this->assertIsArray($savedResponse->answer);
        $this->assertCount(3, $savedResponse->answer);
    }

    /**
     * Test validation for invalid sleep duration
     */
    public function test_validates_sleep_duration_selection(): void
    {
        $sleepQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '평균 수면 시간은 얼마나 되나요?')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $sleepQuestion->id => '잘못된시간',
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test validation for invalid job type
     */
    public function test_validates_job_type_selection(): void
    {
        $jobQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '현재 직업/활동 유형은?')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $jobQuestion->id => '잘못된직업',
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test validation rejects invalid meal times in multi-select
     */
    public function test_validates_meal_times_in_multi_select(): void
    {
        $mealTimeQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '주로 식사하는 시간대를 선택해주세요')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $mealTimeQuestion->id => ['아침 (06:00-09:00)', '잘못된시간대'],
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test can retrieve submitted lifestyle answers
     */
    public function test_can_retrieve_submitted_lifestyle(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::LIFESTYLE)
            ->where('is_required', true)
            ->orderBy('order')
            ->get();

        $answers = [
            $questions[0]->id => '7-8시간',
            $questions[1]->id => '3회',
            $questions[2]->id => ['아침 (06:00-09:00)', '점심 (12:00-14:00)'],
            $questions[3]->id => '거의 안 먹음',
            $questions[4]->id => '사무직 (주로 앉아서 근무)',
            $questions[5]->id => '보통',
        ];

        // Submit
        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        // Retrieve
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/responses?step=3");

        $response->assertStatus(200)
            ->assertJsonCount(6, 'data.responses');
    }

    /**
     * Test can update lifestyle answers
     */
    public function test_can_update_lifestyle_answers(): void
    {
        $sleepQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '평균 수면 시간은 얼마나 되나요?')
            ->first();

        // First submission
        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $sleepQuestion->id => '5시간 미만',
                ],
            ]);

        // Update
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $sleepQuestion->id => '7-8시간',
                ],
            ]);

        $response->assertStatus(200);

        // Verify updated value
        $savedResponse = UserSurveyResponse::where('user_id', $this->user->id)
            ->where('survey_question_id', $sleepQuestion->id)
            ->first();

        $this->assertEquals('7-8시간', $savedResponse->answer['value']);
    }

    /**
     * Test lifestyle contributes to overall progress
     */
    public function test_lifestyle_contributes_to_overall_progress(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::LIFESTYLE)
            ->where('is_required', true)
            ->orderBy('order')
            ->get();

        // Submit all required questions
        $answers = [];
        foreach ($questions as $question) {
            if ($question->question_type === QuestionType::SELECT) {
                $answers[$question->id] = $question->options[0];
            } elseif ($question->question_type === QuestionType::MULTI_SELECT) {
                $answers[$question->id] = [$question->options[0]];
            }
        }

        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        // Check progress
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/progress");

        $response->assertStatus(200)
            ->assertJsonPath('data.answered_questions', count($answers));
    }

    /**
     * Test step 3 is marked complete when all required questions answered
     */
    public function test_step_3_marked_complete_when_all_required_answered(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::LIFESTYLE)
            ->where('is_required', true)
            ->orderBy('order')
            ->get();

        $answers = [];
        foreach ($questions as $question) {
            if ($question->question_type === QuestionType::SELECT) {
                $answers[$question->id] = $question->options[0];
            } elseif ($question->question_type === QuestionType::MULTI_SELECT) {
                $answers[$question->id] = [$question->options[0]];
            }
        }

        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/progress");

        $response->assertStatus(200)
            ->assertJsonPath('data.step_progress.2.is_complete', true);
    }

    /**
     * Test realistic lifestyle pattern responses
     */
    public function test_realistic_lifestyle_pattern(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::LIFESTYLE)
            ->orderBy('order')
            ->get();

        // Realistic office worker pattern
        $answers = [
            $questions[0]->id => '6-7시간',                              // 평균 수면
            $questions[1]->id => '3회',                                  // 식사 횟수
            $questions[2]->id => ['아침 (06:00-09:00)', '점심 (12:00-14:00)', '저녁 (18:00-21:00)'],
            $questions[3]->id => '주 1-2회',                             // 야식
            $questions[4]->id => '사무직 (주로 앉아서 근무)',
            $questions[5]->id => '높음',                                 // 스트레스
            $questions[6]->id => '1L-1.5L',                              // 물
            $questions[7]->id => '주 1-2회',                             // 음주
            $questions[8]->id => '비흡연',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        $response->assertStatus(200);
    }
}
