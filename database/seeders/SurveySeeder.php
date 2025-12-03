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

        // Step 2: 목표 설정 질문들
        $this->createGoalSettingQuestions($survey);

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

    /**
     * Step 2: 목표 설정 질문 생성
     */
    private function createGoalSettingQuestions(Survey $survey): void
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
                'survey_id' => $survey->id,
                'step' => SurveyStep::GOAL_SETTING,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }

        $this->command->info('Step 2 (목표 설정): 6 questions created');
    }
}
