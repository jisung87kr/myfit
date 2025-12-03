<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Enums\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicInfoSurveyTest extends TestCase
{
    use RefreshDatabase;

    private Survey $survey;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->survey = Survey::factory()->create(['is_active' => true]);

        // Create basic info questions matching the seeder
        $this->createBasicInfoQuestions();
    }

    private function createBasicInfoQuestions(): void
    {
        $questions = [
            [
                'question_text' => '성별을 선택해주세요',
                'question_type' => QuestionType::SELECT,
                'options' => ['남성', '여성'],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '생년월일을 입력해주세요 (예: 1990)',
                'question_type' => QuestionType::NUMBER,
                'options' => null,
                'is_required' => true,
                'order' => 2,
            ],
            [
                'question_text' => '키를 입력해주세요 (cm)',
                'question_type' => QuestionType::NUMBER,
                'options' => null,
                'is_required' => true,
                'order' => 3,
            ],
            [
                'question_text' => '현재 체중을 입력해주세요 (kg)',
                'question_type' => QuestionType::NUMBER,
                'options' => null,
                'is_required' => true,
                'order' => 4,
            ],
            [
                'question_text' => '목표 체중을 입력해주세요 (kg)',
                'question_type' => QuestionType::NUMBER,
                'options' => null,
                'is_required' => true,
                'order' => 5,
            ],
            [
                'question_text' => '평소 활동량을 선택해주세요',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '매우 낮음 (거의 운동 안함, 주로 앉아서 생활)',
                    '낮음 (주 1-2회 가벼운 운동)',
                    '보통 (주 3-4회 규칙적인 운동)',
                    '높음 (주 5-6회 강도 높은 운동)',
                    '매우 높음 (매일 고강도 운동, 육체노동)',
                ],
                'is_required' => true,
                'order' => 6,
            ],
            [
                'question_text' => '현재 체형에 대해 어떻게 생각하시나요?',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '매우 마른 편',
                    '마른 편',
                    '표준',
                    '통통한 편',
                    '비만',
                ],
                'is_required' => false,
                'order' => 7,
            ],
            [
                'question_text' => '다이어트 경험이 있으신가요?',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '없음',
                    '1-2회',
                    '3-5회',
                    '5회 이상',
                ],
                'is_required' => false,
                'order' => 8,
            ],
        ];

        foreach ($questions as $questionData) {
            SurveyQuestion::create([
                'survey_id' => $this->survey->id,
                'step' => SurveyStep::BASIC_INFO,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }
    }

    /**
     * Test getting basic info questions
     */
    public function test_can_get_basic_info_questions(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/questions?step=1");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'step' => 1,
                    'step_name' => '기본 정보',
                ],
            ])
            ->assertJsonCount(8, 'data.questions');
    }

    /**
     * Test submitting complete basic info
     */
    public function test_can_submit_complete_basic_info(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::BASIC_INFO)
            ->orderBy('order')
            ->get();

        $answers = [
            $questions[0]->id => '남성',              // 성별
            $questions[1]->id => 1990,               // 생년월일
            $questions[2]->id => 175,                // 키
            $questions[3]->id => 80,                 // 현재 체중
            $questions[4]->id => 70,                 // 목표 체중
            $questions[5]->id => '보통 (주 3-4회 규칙적인 운동)', // 활동량
            $questions[6]->id => '통통한 편',         // 체형 인식 (선택)
            $questions[7]->id => '1-2회',            // 다이어트 경험 (선택)
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
                    'saved_count' => 8,
                ],
            ]);

        // Check progress is 100% for step 1
        $progressResponse = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/progress");

        $progressResponse->assertStatus(200)
            ->assertJsonPath('data.step_progress.0.is_complete', true);
    }

    /**
     * Test submitting only required fields
     */
    public function test_can_submit_only_required_basic_info(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::BASIC_INFO)
            ->orderBy('order')
            ->get();

        // Only required fields
        $answers = [
            $questions[0]->id => '여성',              // 성별
            $questions[1]->id => 1995,               // 생년월일
            $questions[2]->id => 162,                // 키
            $questions[3]->id => 58,                 // 현재 체중
            $questions[4]->id => 52,                 // 목표 체중
            $questions[5]->id => '낮음 (주 1-2회 가벼운 운동)', // 활동량
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
     * Test validation for invalid gender option
     */
    public function test_validates_gender_selection(): void
    {
        $genderQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '성별을 선택해주세요')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $genderQuestion->id => '잘못된성별',
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test validation for invalid activity level
     */
    public function test_validates_activity_level_selection(): void
    {
        $activityQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '평소 활동량을 선택해주세요')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $activityQuestion->id => '잘못된활동량',
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test realistic weight values
     */
    public function test_accepts_realistic_weight_values(): void
    {
        $weightQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '현재 체중을 입력해주세요 (kg)')
            ->first();

        // Valid weight
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $weightQuestion->id => 65.5,
                ],
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test realistic height values
     */
    public function test_accepts_realistic_height_values(): void
    {
        $heightQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '키를 입력해주세요 (cm)')
            ->first();

        // Valid height
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $heightQuestion->id => 170,
                ],
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test can retrieve submitted basic info
     */
    public function test_can_retrieve_submitted_basic_info(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::BASIC_INFO)
            ->orderBy('order')
            ->get();

        $answers = [
            $questions[0]->id => '남성',
            $questions[1]->id => 1990,
            $questions[2]->id => 175,
            $questions[3]->id => 80,
            $questions[4]->id => 70,
            $questions[5]->id => '보통 (주 3-4회 규칙적인 운동)',
        ];

        // Submit
        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => $answers,
            ]);

        // Retrieve
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/responses?step=1");

        $response->assertStatus(200)
            ->assertJsonCount(6, 'data.responses');
    }

    /**
     * Test can update basic info answers
     */
    public function test_can_update_basic_info_answers(): void
    {
        $weightQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '현재 체중을 입력해주세요 (kg)')
            ->first();

        // First submission
        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $weightQuestion->id => 80,
                ],
            ]);

        // Update
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [
                    $weightQuestion->id => 78,
                ],
            ]);

        $response->assertStatus(200);

        // Verify updated value
        $this->assertDatabaseHas('user_survey_responses', [
            'user_id' => $this->user->id,
            'survey_question_id' => $weightQuestion->id,
        ]);
    }

    /**
     * Test basic info contributes to overall progress
     */
    public function test_basic_info_contributes_to_overall_progress(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::BASIC_INFO)
            ->where('is_required', true)
            ->orderBy('order')
            ->get();

        // Submit all required questions
        $answers = [];
        foreach ($questions as $index => $question) {
            if ($question->question_type === QuestionType::SELECT) {
                $answers[$question->id] = $question->options[0];
            } else {
                $answers[$question->id] = $index === 1 ? 1990 : 70;
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
}
