<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Enums\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 기존 설문 데이터 삭제 (개발 환경용)
        SurveyQuestion::query()->delete();
        Survey::query()->delete();

        // 메인 온보딩 설문 생성
        $survey = Survey::create([
            'title' => '맞춤 다이어트 플랜 생성 설문',
            'description' => '당신에게 딱 맞는 다이어트 플랜을 만들어드리기 위한 5단계 설문입니다. 모든 질문에 솔직하게 답변해주세요.',
            'is_active' => true,
        ]);

        // Step 1: 기본 정보 질문들
        $this->createBasicInfoQuestions($survey);

        $this->command->info('Survey seeded successfully!');
        $this->command->info("Survey ID: {$survey->id}");
        $this->command->info('Total questions: ' . SurveyQuestion::count());
    }

    /**
     * Step 1: 기본 정보 질문 생성
     */
    private function createBasicInfoQuestions(Survey $survey): void
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
                'survey_id' => $survey->id,
                'step' => SurveyStep::BASIC_INFO,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }

        $this->command->info('Step 1 (기본 정보): 8 questions created');
    }
}
