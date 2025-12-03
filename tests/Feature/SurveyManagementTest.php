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

class SurveyManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Survey $survey;
    private SurveyQuestion $question1;
    private SurveyQuestion $question2;
    private SurveyQuestion $step2Question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->survey = Survey::factory()->create(['is_active' => true]);

        // Create Step 1 questions
        $this->question1 = SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'step' => SurveyStep::BASIC_INFO->value,
            'question_text' => '성별을 선택해주세요',
            'question_type' => QuestionType::SELECT,
            'options' => ['남성', '여성'],
            'is_required' => true,
            'order' => 1,
        ]);

        $this->question2 = SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'step' => SurveyStep::BASIC_INFO->value,
            'question_text' => '생년월일을 입력해주세요',
            'question_type' => QuestionType::NUMBER,
            'options' => null,
            'is_required' => true,
            'order' => 2,
        ]);

        // Create Step 2 question
        $this->step2Question = SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'step' => SurveyStep::GOAL_SETTING->value,
            'question_text' => '목표 달성 희망 기간을 선택해주세요',
            'question_type' => QuestionType::SELECT,
            'options' => ['1개월 이내', '1-3개월', '3-6개월'],
            'is_required' => true,
            'order' => 1,
        ]);
    }

    /** @test */
    public function test_can_delete_specific_answer()
    {
        // Given: 답변이 저장되어 있음
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question2->id,
            'answer' => ['value' => 1990],
            'answered_at' => now(),
        ]);

        // When: 특정 질문의 답변 삭제
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/answers/{$this->question1->id}");

        // Then: 성공 응답
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => '답변이 삭제되었습니다.',
            ]);

        // 해당 답변만 삭제되고 다른 답변은 유지
        $this->assertDatabaseMissing('user_survey_responses', [
            'user_id' => $this->user->id,
            'survey_question_id' => $this->question1->id,
        ]);

        $this->assertDatabaseHas('user_survey_responses', [
            'user_id' => $this->user->id,
            'survey_question_id' => $this->question2->id,
        ]);
    }

    /** @test */
    public function test_cannot_delete_nonexistent_answer()
    {
        // When: 존재하지 않는 답변 삭제 시도
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/answers/{$this->question1->id}");

        // Then: 404 응답
        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => '삭제할 답변이 없습니다.',
            ]);
    }

    /** @test */
    public function test_cannot_delete_answer_from_different_survey()
    {
        // Given: 다른 설문의 질문
        $otherSurvey = Survey::factory()->create(['is_active' => true]);
        $otherQuestion = SurveyQuestion::create([
            'survey_id' => $otherSurvey->id,
            'step' => SurveyStep::BASIC_INFO->value,
            'question_text' => '다른 설문의 질문',
            'question_type' => QuestionType::TEXT,
            'options' => null,
            'is_required' => true,
            'order' => 1,
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $otherSurvey->id,
            'survey_question_id' => $otherQuestion->id,
            'answer' => ['value' => '답변'],
            'answered_at' => now(),
        ]);

        // When: 현재 설문에서 다른 설문의 질문 ID로 삭제 시도
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/answers/{$otherQuestion->id}");

        // Then: 404 에러 (질문이 해당 설문에 속하지 않음)
        $response->assertNotFound();
    }

    /** @test */
    public function test_can_reset_entire_survey()
    {
        // Given: 여러 답변이 저장되어 있음
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question2->id,
            'answer' => ['value' => 1990],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->step2Question->id,
            'answer' => ['value' => '1-3개월'],
            'answered_at' => now(),
        ]);

        // When: 설문 초기화
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/reset");

        // Then: 모든 답변 삭제
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => '설문이 초기화되었습니다.',
                'data' => [
                    'deleted_count' => 3,
                ],
            ]);

        // 모든 답변이 삭제됨
        $this->assertEquals(0, UserSurveyResponse::where('user_id', $this->user->id)
            ->where('survey_id', $this->survey->id)
            ->count());
    }

    /** @test */
    public function test_reset_survey_returns_zero_if_no_responses()
    {
        // When: 답변이 없는 상태에서 초기화
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/reset");

        // Then: 성공 응답 (삭제된 개수 0)
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'deleted_count' => 0,
                ],
            ]);
    }

    /** @test */
    public function test_can_get_survey_status_not_started()
    {
        // When: 답변이 없는 상태에서 상태 조회
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/status");

        // Then: not_started 상태
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'not_started',
                    'total_questions' => 3,
                    'answered_questions' => 0,
                    'percentage' => 0,
                ],
            ]);
    }

    /** @test */
    public function test_can_get_survey_status_in_progress()
    {
        // Given: 일부 답변만 저장
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        // When: 상태 조회
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/status");

        // Then: in_progress 상태
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'in_progress',
                    'total_questions' => 3,
                    'answered_questions' => 1,
                    'percentage' => 33, // 1/3 = 33%
                ],
            ]);
    }

    /** @test */
    public function test_can_get_survey_status_completed()
    {
        // Given: 모든 질문에 답변
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question2->id,
            'answer' => ['value' => 1990],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->step2Question->id,
            'answer' => ['value' => '1-3개월'],
            'answered_at' => now(),
        ]);

        // When: 상태 조회
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/status");

        // Then: completed 상태
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'completed',
                    'total_questions' => 3,
                    'answered_questions' => 3,
                    'percentage' => 100,
                ],
            ]);
    }

    /** @test */
    public function test_can_delete_step_responses()
    {
        // Given: Step 1과 Step 2 답변이 모두 있음
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question2->id,
            'answer' => ['value' => 1990],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->step2Question->id,
            'answer' => ['value' => '1-3개월'],
            'answered_at' => now(),
        ]);

        // When: Step 1 답변만 삭제
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/steps/" . SurveyStep::BASIC_INFO->value, [
                'confirm' => true,
            ]);

        // Then: Step 1 답변만 삭제되고 Step 2는 유지
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => '기본 정보 단계의 답변이 삭제되었습니다.',
                'data' => [
                    'step' => SurveyStep::BASIC_INFO->value,
                    'deleted_count' => 2,
                ],
            ]);

        // Step 1 답변 삭제 확인
        $this->assertEquals(0, UserSurveyResponse::where('user_id', $this->user->id)
            ->whereHas('question', function ($q) {
                $q->where('step', SurveyStep::BASIC_INFO->value);
            })
            ->count());

        // Step 2 답변 유지 확인
        $this->assertEquals(1, UserSurveyResponse::where('user_id', $this->user->id)
            ->whereHas('question', function ($q) {
                $q->where('step', SurveyStep::GOAL_SETTING->value);
            })
            ->count());
    }

    /** @test */
    public function test_delete_step_responses_requires_confirm()
    {
        // Given: 답변이 저장되어 있음
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        // When: confirm 없이 삭제 시도
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/steps/" . SurveyStep::BASIC_INFO->value);

        // Then: 검증 에러
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirm']);

        // 답변이 삭제되지 않음
        $this->assertDatabaseHas('user_survey_responses', [
            'user_id' => $this->user->id,
            'survey_question_id' => $this->question1->id,
        ]);
    }

    /** @test */
    public function test_delete_step_responses_validates_step_number()
    {
        // When: 유효하지 않은 단계 번호로 삭제 시도
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/steps/999", [
                'confirm' => true,
            ]);

        // Then: 검증 에러
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => '유효하지 않은 단계입니다.',
            ]);
    }

    /** @test */
    public function test_delete_answer_affects_progress_correctly()
    {
        // Given: 2개의 답변 저장
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question2->id,
            'answer' => ['value' => 1990],
            'answered_at' => now(),
        ]);

        // When: 하나의 답변 삭제
        $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/answers/{$this->question1->id}");

        // Then: 진행률이 업데이트됨
        $progressResponse = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/progress");

        $progressResponse->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_questions' => 3,
                    'answered_questions' => 1,
                    'percentage' => 33,
                    'is_complete' => false,
                ],
            ]);
    }

    /** @test */
    public function test_reset_survey_affects_progress_correctly()
    {
        // Given: 답변이 저장되어 있음
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        // When: 설문 초기화
        $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/reset");

        // Then: 진행률이 0으로 초기화됨
        $progressResponse = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/progress");

        $progressResponse->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_questions' => 3,
                    'answered_questions' => 0,
                    'percentage' => 0,
                    'is_complete' => false,
                ],
            ]);
    }

    /** @test */
    public function test_user_cannot_delete_other_users_answers()
    {
        // Given: 다른 사용자의 답변
        $otherUser = User::factory()->create();
        UserSurveyResponse::create([
            'user_id' => $otherUser->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->question1->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        // When: 현재 사용자가 다른 사용자의 답변 삭제 시도
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/surveys/{$this->survey->id}/answers/{$this->question1->id}");

        // Then: 404 응답 (자신의 답변이 없음)
        $response->assertNotFound();

        // 다른 사용자의 답변은 그대로 유지
        $this->assertDatabaseHas('user_survey_responses', [
            'user_id' => $otherUser->id,
            'survey_question_id' => $this->question1->id,
        ]);
    }

    /** @test */
    public function test_requires_authentication_for_all_management_endpoints()
    {
        // Test deleteAnswer
        $response = $this->deleteJson("/api/surveys/{$this->survey->id}/answers/{$this->question1->id}");
        $response->assertUnauthorized();

        // Test resetSurvey
        $response = $this->deleteJson("/api/surveys/{$this->survey->id}/reset");
        $response->assertUnauthorized();

        // Test getStatus
        $response = $this->getJson("/api/surveys/{$this->survey->id}/status");
        $response->assertUnauthorized();

        // Test deleteStepResponses
        $response = $this->deleteJson("/api/surveys/{$this->survey->id}/steps/1", [
            'confirm' => true,
        ]);
        $response->assertUnauthorized();
    }
}
