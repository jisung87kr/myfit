<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Enums\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveySubmission;
use App\Models\User;
use App\Models\UserSurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveySubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Survey $survey;
    private array $questions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->survey = Survey::factory()->create(['is_active' => true]);

        // Create 2 required questions and 1 optional question
        $this->questions['required1'] = SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'step' => SurveyStep::BASIC_INFO->value,
            'question_text' => '성별을 선택해주세요',
            'question_type' => QuestionType::SELECT,
            'options' => ['남성', '여성'],
            'is_required' => true,
            'order' => 1,
        ]);

        $this->questions['required2'] = SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'step' => SurveyStep::BASIC_INFO->value,
            'question_text' => '나이를 입력해주세요',
            'question_type' => QuestionType::NUMBER,
            'options' => null,
            'is_required' => true,
            'order' => 2,
        ]);

        $this->questions['optional'] = SurveyQuestion::create([
            'survey_id' => $this->survey->id,
            'step' => SurveyStep::BASIC_INFO->value,
            'question_text' => '추가 정보',
            'question_type' => QuestionType::TEXT,
            'options' => null,
            'is_required' => false,
            'order' => 3,
        ]);
    }

    /** @test */
    public function test_can_submit_completed_survey()
    {
        // Given: 모든 필수 질문에 답변
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required1']->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required2']->id,
            'answer' => ['value' => 30],
            'answered_at' => now(),
        ]);

        // When: 설문 제출
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/submit");

        // Then: 성공 응답
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => '설문이 성공적으로 제출되었습니다.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'submission_id',
                    'submitted_at',
                    'total_responses',
                    'message',
                ],
            ]);

        // 제출 기록 생성 확인
        $this->assertDatabaseHas('survey_submissions', [
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
        ]);
    }

    /** @test */
    public function test_cannot_submit_incomplete_survey()
    {
        // Given: 필수 질문 중 하나만 답변
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required1']->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        // When: 설문 제출 시도
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/submit");

        // Then: 검증 에러
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => '모든 필수 질문에 답변해주세요.',
            ])
            ->assertJsonStructure([
                'errors' => [
                    'survey',
                    'unanswered_questions',
                ],
            ]);

        // 제출 기록이 생성되지 않음
        $this->assertDatabaseMissing('survey_submissions', [
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
        ]);
    }

    /** @test */
    public function test_cannot_submit_already_submitted_survey()
    {
        // Given: 설문 이미 제출됨
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required1']->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required2']->id,
            'answer' => ['value' => 30],
            'answered_at' => now(),
        ]);

        SurveySubmission::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'completion_data' => ['test' => 'data'],
            'submitted_at' => now(),
        ]);

        // When: 다시 제출 시도
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/submit");

        // Then: 검증 에러
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => '이미 제출한 설문입니다.',
            ]);
    }

    /** @test */
    public function test_can_submit_with_optional_questions_unanswered()
    {
        // Given: 필수 질문만 답변 (선택 질문은 미답변)
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required1']->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required2']->id,
            'answer' => ['value' => 30],
            'answered_at' => now(),
        ]);

        // When: 설문 제출
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/submit");

        // Then: 성공
        $response->assertOk();

        $this->assertDatabaseHas('survey_submissions', [
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
        ]);
    }

    /** @test */
    public function test_submission_includes_snapshot_of_responses()
    {
        // Given: 설문 응답
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required1']->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required2']->id,
            'answer' => ['value' => 30],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['optional']->id,
            'answer' => ['value' => '추가 정보 내용'],
            'answered_at' => now(),
        ]);

        // When: 제출
        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/submit");

        // Then: 제출 데이터에 모든 응답 포함
        $submission = SurveySubmission::where('user_id', $this->user->id)->first();

        $this->assertNotNull($submission);
        $this->assertIsArray($submission->completion_data);
        $this->assertArrayHasKey('responses', $submission->completion_data);
        $this->assertCount(3, $submission->completion_data['responses']);
        $this->assertEquals($this->user->name, $submission->completion_data['submitted_by']);
        $this->assertEquals($this->user->email, $submission->completion_data['submitted_email']);
    }

    /** @test */
    public function test_can_get_submission_info()
    {
        // Given: 설문 제출됨
        $submission = SurveySubmission::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'completion_data' => [
                'responses' => [],
                'total_questions' => 3,
                'answered_questions' => 2,
            ],
            'submitted_at' => now(),
        ]);

        // When: 제출 정보 조회
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/submission");

        // Then: 제출 정보 반환
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $submission->id,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'submitted_at',
                    'completion_data',
                ],
            ]);
    }

    /** @test */
    public function test_returns_404_if_no_submission_exists()
    {
        // When: 제출하지 않은 설문의 제출 정보 조회
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/submission");

        // Then: 404 응답
        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => '제출 기록이 없습니다.',
            ]);
    }

    /** @test */
    public function test_user_cannot_see_other_users_submission()
    {
        // Given: 다른 사용자의 제출
        $otherUser = User::factory()->create();
        SurveySubmission::create([
            'user_id' => $otherUser->id,
            'survey_id' => $this->survey->id,
            'completion_data' => ['test' => 'data'],
            'submitted_at' => now(),
        ]);

        // When: 현재 사용자가 제출 정보 조회
        $response = $this->actingAs($this->user)
            ->getJson("/api/surveys/{$this->survey->id}/submission");

        // Then: 404 (자신의 제출 기록 없음)
        $response->assertNotFound();
    }

    /** @test */
    public function test_requires_authentication_for_submission()
    {
        // Test submit endpoint
        $response = $this->postJson("/api/surveys/{$this->survey->id}/submit");
        $response->assertUnauthorized();

        // Test get submission endpoint
        $response = $this->getJson("/api/surveys/{$this->survey->id}/submission");
        $response->assertUnauthorized();
    }

    /** @test */
    public function test_submission_stores_correct_timestamp()
    {
        // Given: 설문 응답 완료
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required1']->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required2']->id,
            'answer' => ['value' => 30],
            'answered_at' => now(),
        ]);

        $beforeSubmit = now();

        // When: 제출
        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/submit");

        $afterSubmit = now();

        // Then: 제출 시간이 정확함
        $submission = SurveySubmission::where('user_id', $this->user->id)->first();
        $this->assertNotNull($submission);
        $this->assertTrue(
            $submission->submitted_at->between($beforeSubmit, $afterSubmit)
        );
    }

    /** @test */
    public function test_submission_is_unique_per_user_per_survey()
    {
        // Given: 첫 번째 제출 완료
        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required1']->id,
            'answer' => ['value' => '남성'],
            'answered_at' => now(),
        ]);

        UserSurveyResponse::create([
            'user_id' => $this->user->id,
            'survey_id' => $this->survey->id,
            'survey_question_id' => $this->questions['required2']->id,
            'answer' => ['value' => 30],
            'answered_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/submit");

        // Then: DB에 하나의 제출 기록만 존재
        $this->assertEquals(1, SurveySubmission::where('user_id', $this->user->id)
            ->where('survey_id', $this->survey->id)
            ->count());

        // 다시 제출 시도하면 실패
        $response = $this->actingAs($this->user)
            ->postJson("/api/surveys/{$this->survey->id}/submit");

        $response->assertStatus(422);

        // 여전히 하나의 제출 기록만 존재
        $this->assertEquals(1, SurveySubmission::where('user_id', $this->user->id)
            ->where('survey_id', $this->survey->id)
            ->count());
    }
}
