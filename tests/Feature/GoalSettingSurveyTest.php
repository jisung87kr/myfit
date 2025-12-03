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

class GoalSettingSurveyTest extends TestCase
{
    use RefreshDatabase;

    private Survey $survey;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->survey = Survey::factory()->create(['is_active' => true]);

        // Create goal setting questions matching the seeder
        $this->createGoalSettingQuestions();
    }

    private function createGoalSettingQuestions(): void
    {
        $questions = [
            [
                'question_text' => '목표 체중 달성 희망 기간은 얼마나 되나요?',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '1개월 이내',
                    '1-3개월',
                    '3-6개월',
                    '6개월-1년',
                    '1년 이상',
                    '기간 상관없이 천천히',
                ],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '다이어트의 주요 목표는 무엇인가요?',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => [
                    '체중 감량',
                    '체지방 감소',
                    '근육량 증가',
                    '건강 개선',
                    '체형 관리',
                    '자신감 향상',
                ],
                'is_required' => true,
                'order' => 2,
            ],
            [
                'question_text' => '어떤 방식의 다이어트를 선호하시나요?',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '식단 조절 중심',
                    '운동 중심',
                    '식단과 운동 병행',
                    '간헐적 단식',
                    '저탄수화물 (키토)',
                    '전문가 상담 후 결정',
                ],
                'is_required' => true,
                'order' => 3,
            ],
            [
                'question_text' => '하루에 운동할 수 있는 시간은 얼마나 되나요?',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '운동 불가',
                    '15-30분',
                    '30분-1시간',
                    '1-2시간',
                    '2시간 이상',
                ],
                'is_required' => true,
                'order' => 4,
            ],
            [
                'question_text' => '선호하는 운동 유형은 무엇인가요?',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => [
                    '유산소 (달리기, 자전거 등)',
                    '근력 운동 (웨이트)',
                    '홈트레이닝',
                    '요가/필라테스',
                    '수영',
                    '구기 종목',
                    '등산/산책',
                    '운동 안함',
                ],
                'is_required' => false,
                'order' => 5,
            ],
            [
                'question_text' => '다이어트 성공을 위해 가장 중요하다고 생각하는 것은?',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '꾸준한 실천',
                    '철저한 식단 관리',
                    '규칙적인 운동',
                    '충분한 휴식과 수면',
                    '스트레스 관리',
                    '전문가의 도움',
                ],
                'is_required' => false,
                'order' => 6,
            ],
        ];

        foreach ($questions as $questionData) {
            SurveyQuestion::create([
                'survey_id' => $this->survey->id,
                'step' => SurveyStep::GOAL_SETTING,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }
    }

    /**
     * Test getting goal setting questions
     */
    public function test_can_get_goal_setting_questions(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/questions?step=2");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'step' => 2,
                    'step_name' => '목표 설정',
                ],
            ])
            ->assertJsonCount(6, 'data.questions');
    }

    /**
     * Test submitting complete goal setting answers
     */
    public function test_can_submit_complete_goal_setting(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::GOAL_SETTING)
            ->orderBy('order')
            ->get();

        $answers = [
            $questions[0]->id => '3-6개월',                              // 목표 기간
            $questions[1]->id => ['체중 감량', '체지방 감소', '건강 개선'],  // 주요 목표
            $questions[2]->id => '식단과 운동 병행',                      // 선호 방식
            $questions[3]->id => '30분-1시간',                           // 운동 시간
            $questions[4]->id => ['유산소 (달리기, 자전거 등)', '홈트레이닝'], // 운동 유형
            $questions[5]->id => '꾸준한 실천',                          // 중요 요소
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
                    'saved_count' => 6,
                ],
            ]);
    }

    /**
     * Test submitting only required goal setting fields
     */
    public function test_can_submit_only_required_goal_setting(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::GOAL_SETTING)
            ->where('is_required', true)
            ->orderBy('order')
            ->get();

        $answers = [
            $questions[0]->id => '1-3개월',
            $questions[1]->id => ['체중 감량', '건강 개선'],
            $questions[2]->id => '식단 조절 중심',
            $questions[3]->id => '15-30분',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'saved_count' => 4,
                ],
            ]);
    }

    /**
     * Test multi-select for diet goals
     */
    public function test_can_submit_multiple_diet_goals(): void
    {
        $goalQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '다이어트의 주요 목표는 무엇인가요?')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $goalQuestion->id => ['체중 감량', '체지방 감소', '근육량 증가'],
                ],
            ]);

        $response->assertStatus(200);

        $savedResponse = UserSurveyResponse::where('user_id', $this->user->id)
            ->where('survey_question_id', $goalQuestion->id)
            ->first();

        $this->assertIsArray($savedResponse->answer);
        $this->assertCount(3, $savedResponse->answer);
    }

    /**
     * Test validation for invalid goal period
     */
    public function test_validates_goal_period_selection(): void
    {
        $periodQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '목표 체중 달성 희망 기간은 얼마나 되나요?')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $periodQuestion->id => '잘못된기간',
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test validation for invalid diet method
     */
    public function test_validates_diet_method_selection(): void
    {
        $methodQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '어떤 방식의 다이어트를 선호하시나요?')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $methodQuestion->id => '잘못된방식',
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test multi-select for exercise types
     */
    public function test_can_submit_multiple_exercise_types(): void
    {
        $exerciseQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '선호하는 운동 유형은 무엇인가요?')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $exerciseQuestion->id => ['유산소 (달리기, 자전거 등)', '근력 운동 (웨이트)', '수영'],
                ],
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test validation rejects invalid exercise types in multi-select
     */
    public function test_validates_exercise_types_in_multi_select(): void
    {
        $exerciseQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '선호하는 운동 유형은 무엇인가요?')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $exerciseQuestion->id => ['유산소 (달리기, 자전거 등)', '잘못된운동'],
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test can retrieve submitted goal setting answers
     */
    public function test_can_retrieve_submitted_goal_setting(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::GOAL_SETTING)
            ->where('is_required', true)
            ->orderBy('order')
            ->get();

        $answers = [
            $questions[0]->id => '3-6개월',
            $questions[1]->id => ['체중 감량'],
            $questions[2]->id => '식단과 운동 병행',
            $questions[3]->id => '1-2시간',
        ];

        // Submit
        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        // Retrieve
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/responses?step=2");

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data.responses');
    }

    /**
     * Test can update goal setting answers
     */
    public function test_can_update_goal_setting_answers(): void
    {
        $periodQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '목표 체중 달성 희망 기간은 얼마나 되나요?')
            ->first();

        // First submission
        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $periodQuestion->id => '1개월 이내',
                ],
            ]);

        // Update
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $periodQuestion->id => '3-6개월',
                ],
            ]);

        $response->assertStatus(200);

        // Verify updated value
        $savedResponse = UserSurveyResponse::where('user_id', $this->user->id)
            ->where('survey_question_id', $periodQuestion->id)
            ->first();

        $this->assertEquals('3-6개월', $savedResponse->answer['value']);
    }

    /**
     * Test goal setting contributes to overall progress
     */
    public function test_goal_setting_contributes_to_overall_progress(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::GOAL_SETTING)
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
     * Test step 2 is marked complete when all required questions answered
     */
    public function test_step_2_marked_complete_when_all_required_answered(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::GOAL_SETTING)
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
            ->assertJsonPath('data.step_progress.1.is_complete', true);
    }
}
