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

class SurveyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test unauthenticated user cannot access survey endpoints
     */
    public function test_unauthenticated_user_cannot_access_surveys(): void
    {
        $survey = Survey::factory()->create();

        $this->getJson('/api/surveys')->assertStatus(401);
        $this->getJson("/api/surveys/{$survey->id}/questions?step=1")->assertStatus(401);
        $this->postJson("/api/surveys/{$survey->id}/answers", [])->assertStatus(401);
        $this->getJson("/api/surveys/{$survey->id}/responses")->assertStatus(401);
        $this->getJson("/api/surveys/{$survey->id}/progress")->assertStatus(401);
        $this->getJson("/api/surveys/{$survey->id}/summary")->assertStatus(401);
    }

    /**
     * Test get active survey
     */
    public function test_get_active_survey(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'title' => '온보딩 설문',
            'description' => '맞춤 다이어트 플랜을 위한 설문',
            'is_active' => true,
        ]);

        SurveyQuestion::factory()->count(10)->create(['survey_id' => $survey->id]);

        $response = $this->actingAs($user)->getJson('/api/surveys');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'survey' => [
                        'id' => $survey->id,
                        'title' => '온보딩 설문',
                        'description' => '맞춤 다이어트 플랜을 위한 설문',
                        'total_questions' => 10,
                    ],
                ],
            ]);
    }

    /**
     * Test no active survey returns error
     */
    public function test_no_active_survey_returns_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/surveys');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => '활성화된 설문이 없습니다.',
            ]);
    }

    /**
     * Test get questions by step
     */
    public function test_get_questions_by_step(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'step' => SurveyStep::BASIC_INFO,
            'question_text' => '현재 체중은 몇 kg인가요?',
            'question_type' => QuestionType::NUMBER,
            'order' => 1,
        ]);

        SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'step' => SurveyStep::BASIC_INFO,
            'question_text' => '성별을 선택해주세요',
            'question_type' => QuestionType::SELECT,
            'options' => ['남성', '여성'],
            'order' => 2,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/surveys/{$survey->id}/questions?step=1");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'step' => 1,
                    'step_name' => '기본 정보',
                ],
            ])
            ->assertJsonCount(2, 'data.questions');
    }

    /**
     * Test get questions with invalid step
     */
    public function test_get_questions_with_invalid_step(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->getJson("/api/surveys/{$survey->id}/questions?step=10");

        $response->assertStatus(422);
    }

    /**
     * Test submit valid answers
     */
    public function test_submit_valid_answers(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $question1 = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'question_type' => QuestionType::NUMBER,
            'question_text' => '체중',
        ]);

        $question2 = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'question_type' => QuestionType::TEXT,
            'question_text' => '이름',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/surveys/{$survey->id}/answers", [
                'answers' => [
                    $question1->id => 70,
                    $question2->id => '홍길동',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '답변이 저장되었습니다.',
                'data' => [
                    'saved_count' => 2,
                ],
            ]);

        $this->assertDatabaseHas('user_survey_responses', [
            'user_id' => $user->id,
            'survey_id' => $survey->id,
            'survey_question_id' => $question1->id,
        ]);

        $this->assertDatabaseHas('user_survey_responses', [
            'user_id' => $user->id,
            'survey_id' => $survey->id,
            'survey_question_id' => $question2->id,
        ]);
    }

    /**
     * Test submit answers with select type validation
     */
    public function test_submit_answers_with_select_validation(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $question = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'question_type' => QuestionType::SELECT,
            'options' => ['옵션1', '옵션2', '옵션3'],
        ]);

        // Valid answer
        $response = $this->actingAs($user)
            ->postJson("/api/surveys/{$survey->id}/answers", [
                'answers' => [
                    $question->id => '옵션1',
                ],
            ]);

        $response->assertStatus(200);

        // Invalid answer (not in options)
        $response = $this->actingAs($user)
            ->postJson("/api/surveys/{$survey->id}/answers", [
                'answers' => [
                    $question->id => '잘못된옵션',
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test submit answers with multi-select type
     */
    public function test_submit_answers_with_multi_select(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $question = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'question_type' => QuestionType::MULTI_SELECT,
            'options' => ['A', 'B', 'C', 'D'],
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/surveys/{$survey->id}/answers", [
                'answers' => [
                    $question->id => ['A', 'C'],
                ],
            ]);

        $response->assertStatus(200);

        $savedResponse = UserSurveyResponse::where('user_id', $user->id)
            ->where('survey_question_id', $question->id)
            ->first();

        $this->assertIsArray($savedResponse->answer);
    }

    /**
     * Test update existing answer
     */
    public function test_update_existing_answer(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $question = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'question_type' => QuestionType::NUMBER,
        ]);

        // First submission
        $this->actingAs($user)
            ->postJson("/api/surveys/{$survey->id}/answers", [
                'answers' => [$question->id => 70],
            ]);

        $this->assertDatabaseHas('user_survey_responses', [
            'user_id' => $user->id,
            'survey_question_id' => $question->id,
        ]);

        // Update submission
        $this->actingAs($user)
            ->postJson("/api/surveys/{$survey->id}/answers", [
                'answers' => [$question->id => 75],
            ]);

        // Should still have only one record
        $this->assertDatabaseCount('user_survey_responses', 1);

        $response = UserSurveyResponse::where('user_id', $user->id)
            ->where('survey_question_id', $question->id)
            ->first();

        $this->assertEquals(75, $response->answer['value']);
    }

    /**
     * Test get user responses
     */
    public function test_get_user_responses(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $question1 = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'step' => SurveyStep::BASIC_INFO,
        ]);

        $question2 = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'step' => SurveyStep::GOAL_SETTING,
        ]);

        UserSurveyResponse::create([
            'user_id' => $user->id,
            'survey_id' => $survey->id,
            'survey_question_id' => $question1->id,
            'answer' => ['value' => '답변1'],
        ]);

        UserSurveyResponse::create([
            'user_id' => $user->id,
            'survey_id' => $survey->id,
            'survey_question_id' => $question2->id,
            'answer' => ['value' => '답변2'],
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/surveys/{$survey->id}/responses");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.responses');
    }

    /**
     * Test get user responses filtered by step
     */
    public function test_get_user_responses_filtered_by_step(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $question1 = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'step' => SurveyStep::BASIC_INFO,
        ]);

        $question2 = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'step' => SurveyStep::GOAL_SETTING,
        ]);

        UserSurveyResponse::create([
            'user_id' => $user->id,
            'survey_id' => $survey->id,
            'survey_question_id' => $question1->id,
            'answer' => ['value' => '답변1'],
        ]);

        UserSurveyResponse::create([
            'user_id' => $user->id,
            'survey_id' => $survey->id,
            'survey_question_id' => $question2->id,
            'answer' => ['value' => '답변2'],
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/surveys/{$survey->id}/responses?step=1");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.responses')
            ->assertJsonFragment(['step' => 1]);
    }

    /**
     * Test get user progress
     */
    public function test_get_user_progress(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        // Create 10 questions
        $questions = SurveyQuestion::factory()->count(10)->create([
            'survey_id' => $survey->id,
            'step' => SurveyStep::BASIC_INFO,
        ]);

        // Answer 5 questions
        foreach ($questions->take(5) as $question) {
            UserSurveyResponse::create([
                'user_id' => $user->id,
                'survey_id' => $survey->id,
                'survey_question_id' => $question->id,
                'answer' => ['value' => 'test'],
            ]);
        }

        $response = $this->actingAs($user)
            ->getJson("/api/surveys/{$survey->id}/progress");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_questions' => 10,
                    'answered_questions' => 5,
                    'percentage' => 50,
                    'is_complete' => false,
                ],
            ]);
    }

    /**
     * Test get survey summary
     */
    public function test_get_survey_summary(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'title' => '온보딩 설문',
            'is_active' => true,
        ]);

        $question = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'step' => SurveyStep::BASIC_INFO,
        ]);

        UserSurveyResponse::create([
            'user_id' => $user->id,
            'survey_id' => $survey->id,
            'survey_question_id' => $question->id,
            'answer' => ['value' => 'test'],
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/surveys/{$survey->id}/summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'survey' => ['id', 'title', 'description'],
                    'progress' => [
                        'total_questions',
                        'answered_questions',
                        'percentage',
                        'is_complete',
                        'step_progress',
                    ],
                    'next_step',
                    'total_responses',
                ],
            ])
            ->assertJson([
                'data' => [
                    'survey' => [
                        'title' => '온보딩 설문',
                    ],
                    'total_responses' => 1,
                ],
            ]);
    }

    /**
     * Test validation requires answers array
     */
    public function test_validation_requires_answers_array(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->postJson("/api/surveys/{$survey->id}/answers", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['answers']);
    }

    /**
     * Test cannot submit answer for question from different survey
     */
    public function test_cannot_submit_answer_for_different_survey(): void
    {
        $user = User::factory()->create();
        $survey1 = Survey::factory()->create(['is_active' => true]);
        $survey2 = Survey::factory()->create(['is_active' => true]);

        $questionFromSurvey2 = SurveyQuestion::factory()->create([
            'survey_id' => $survey2->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/surveys/{$survey1->id}/answers", [
                'answers' => [
                    $questionFromSurvey2->id => 'answer',
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test required question validation
     */
    public function test_required_question_validation(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create(['is_active' => true]);

        $question = SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'question_type' => QuestionType::TEXT,
            'is_required' => true,
        ]);

        // Empty answer for required question should fail
        $response = $this->actingAs($user)
            ->postJson("/api/surveys/{$survey->id}/answers", [
                'answers' => [
                    $question->id => '',
                ],
            ]);

        $response->assertStatus(422);
    }
}
