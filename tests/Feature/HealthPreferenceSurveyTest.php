<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Enums\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthPreferenceSurveyTest extends TestCase
{
    use RefreshDatabase;

    private Survey $survey;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->survey = Survey::factory()->create(['is_active' => true]);
        $this->createHealthPreferenceQuestions();
    }

    private function createHealthPreferenceQuestions(): void
    {
        $questions = [
            [
                'question_text' => '알레르기가 있거나 먹지 못하는 음식이 있나요?',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => ['없음', '유제품 (우유, 치즈 등)', '해산물', '견과류', '계란', '밀가루 (글루텐)', '콩류', '기타'],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '선호하는 음식 종류를 선택해주세요 (복수 선택)',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => ['한식', '양식', '중식', '일식', '샐러드', '과일', '육류', '생선', '채소', '곡물/잡곡'],
                'is_required' => true,
                'order' => 2,
            ],
            [
                'question_text' => '기피하는 음식이 있나요?',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => ['없음', '매운 음식', '기름진 음식', '날것 (회, 육회 등)', '내장류', '특정 채소 (쓴맛 등)', '유제품', '해산물'],
                'is_required' => false,
                'order' => 3,
            ],
            [
                'question_text' => '현재 앓고 있거나 과거에 진단받은 질환이 있나요?',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => ['없음', '당뇨병', '고혈압', '고지혈증', '갑상선 질환', '소화기 질환', '심혈관 질환', '관절염', '기타'],
                'is_required' => true,
                'order' => 4,
            ],
            [
                'question_text' => '현재 복용 중인 약이나 영양제가 있나요?',
                'question_type' => QuestionType::SELECT,
                'options' => ['없음', '처방약 복용 중', '영양제만 복용 중', '처방약과 영양제 모두 복용'],
                'is_required' => false,
                'order' => 5,
            ],
            [
                'question_text' => '식단 제한이 필요한 종교나 신념이 있나요?',
                'question_type' => QuestionType::SELECT,
                'options' => ['없음', '채식주의 (비건)', '채식주의 (락토/오보)', '할랄', '코셔', '기타'],
                'is_required' => false,
                'order' => 6,
            ],
            [
                'question_text' => '외식 빈도는 어느 정도인가요?',
                'question_type' => QuestionType::SELECT,
                'options' => ['거의 안 함 (주 0-1회)', '가끔 (주 2-3회)', '자주 (주 4-5회)', '매우 자주 (주 6회 이상)', '거의 매끼'],
                'is_required' => true,
                'order' => 7,
            ],
            [
                'question_text' => '간식을 주로 언제 먹나요?',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => ['먹지 않음', '오전', '오후', '저녁 후', '불규칙적'],
                'is_required' => false,
                'order' => 8,
            ],
        ];

        foreach ($questions as $questionData) {
            SurveyQuestion::create([
                'survey_id' => $this->survey->id,
                'step' => SurveyStep::HEALTH_PREFERENCE,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }
    }

    public function test_can_get_health_preference_questions(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/questions?step=4");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'data' => ['step' => 4, 'step_name' => '건강 & 선호도']])
            ->assertJsonCount(8, 'data.questions');
    }

    public function test_can_submit_complete_health_preference(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::HEALTH_PREFERENCE)->orderBy('order')->get();

        $answers = [
            $questions[0]->id => ['없음'],
            $questions[1]->id => ['한식', '샐러드', '채소'],
            $questions[2]->id => ['없음'],
            $questions[3]->id => ['없음'],
            $questions[4]->id => '영양제만 복용 중',
            $questions[5]->id => '없음',
            $questions[6]->id => '가끔 (주 2-3회)',
            $questions[7]->id => ['오후', '저녁 후'],
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", ['answers' => $answers]);

        $response->assertStatus(200)->assertJson(['success' => true, 'data' => ['saved_count' => 8]]);
    }

    public function test_can_submit_only_required_health_preference(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::HEALTH_PREFERENCE)->where('is_required', true)->orderBy('order')->get();

        $answers = [
            $questions[0]->id => ['없음'],
            $questions[1]->id => ['한식'],
            $questions[2]->id => ['없음'],
            $questions[3]->id => '거의 안 함 (주 0-1회)',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", ['answers' => $answers]);

        $response->assertStatus(200)->assertJson(['success' => true, 'data' => ['saved_count' => 4]]);
    }

    public function test_can_submit_multiple_allergies(): void
    {
        $allergyQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '알레르기가 있거나 먹지 못하는 음식이 있나요?')->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [$allergyQuestion->id => ['유제품 (우유, 치즈 등)', '해산물', '견과류']],
            ]);

        $response->assertStatus(200);
    }

    public function test_validates_invalid_allergy_option(): void
    {
        $allergyQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '알레르기가 있거나 먹지 못하는 음식이 있나요?')->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [$allergyQuestion->id => ['없음', '잘못된알레르기']],
            ]);

        $response->assertStatus(422);
    }

    public function test_can_submit_multiple_food_preferences(): void
    {
        $foodQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '선호하는 음식 종류를 선택해주세요 (복수 선택)')->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [$foodQuestion->id => ['한식', '일식', '샐러드', '채소']],
            ]);

        $response->assertStatus(200);
    }

    public function test_validates_medication_selection(): void
    {
        $medicationQuestion = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('question_text', '현재 복용 중인 약이나 영양제가 있나요?')->first();

        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", [
                'answers' => [$medicationQuestion->id => '잘못된옵션'],
            ]);

        $response->assertStatus(422);
    }

    public function test_step_4_marked_complete_when_all_required_answered(): void
    {
        $questions = SurveyQuestion::where('survey_id', $this->survey->id)
            ->where('step', SurveyStep::HEALTH_PREFERENCE)->where('is_required', true)->orderBy('order')->get();

        $answers = [];
        foreach ($questions as $question) {
            $answers[$question->id] = $question->question_type === QuestionType::SELECT
                ? $question->options[0]
                : [$question->options[0]];
        }

        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/answers", ['answers' => $answers]);

        $response = $this->actingAs($this->user)->getJson("/api/surveys/{$this->survey->id}/progress");
        $response->assertStatus(200)->assertJsonPath('data.step_progress.3.is_complete', true);
    }
}
