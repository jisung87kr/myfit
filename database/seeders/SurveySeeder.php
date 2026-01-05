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

        // Step 3: 생활 패턴 질문들
        $this->createLifestyleQuestions($survey);

        // Step 4: 건강 & 선호도 질문들
        $this->createHealthPreferenceQuestions($survey);

        // Step 5: 추가 정보 질문들
        $this->createAdditionalQuestions($survey);

        $this->command->info('Survey seeded successfully!');
        $this->command->info("Survey ID: {$survey->id}");
        $this->command->info('Total questions: ' . SurveyQuestion::count());
    }

    /**
     * Step 1: 기본 정보 질문 생성
     * DietPlanService에서 사용하는 키: 성별, 나이, 현재 체중 (kg), 목표 체중 (kg), 키 (cm)
     */
    private function createBasicInfoQuestions(Survey $survey): void
    {
        $questions = [
            [
                'question_text' => '성별',
                'question_type' => QuestionType::SELECT,
                'options' => ['남성', '여성'],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '나이',
                'question_type' => QuestionType::NUMBER,
                'options' => null,
                'is_required' => true,
                'order' => 2,
            ],
            [
                'question_text' => '키 (cm)',
                'question_type' => QuestionType::NUMBER,
                'options' => null,
                'is_required' => true,
                'order' => 3,
            ],
            [
                'question_text' => '현재 체중 (kg)',
                'question_type' => QuestionType::NUMBER,
                'options' => null,
                'is_required' => true,
                'order' => 4,
            ],
            [
                'question_text' => '목표 체중 (kg)',
                'question_type' => QuestionType::NUMBER,
                'options' => null,
                'is_required' => true,
                'order' => 5,
            ],
            [
                'question_text' => '일일 활동량',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '매우 낮음',
                    '낮음',
                    '보통',
                    '높음',
                    '매우 높음',
                ],
                'is_required' => true,
                'order' => 6,
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

        $this->command->info('Step 1 (기본 정보): ' . count($questions) . ' questions created');
    }

    /**
     * Step 2: 목표 설정 질문 생성
     * DietPlanService에서 사용하는 키: 주요 목표, 희망 감량 기간
     */
    private function createGoalSettingQuestions(Survey $survey): void
    {
        $questions = [
            [
                'question_text' => '주요 목표',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '체중 감량',
                    '체지방 감소',
                    '근육량 증가',
                    '건강 개선',
                    '체형 유지',
                ],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '희망 감량 기간',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '4주',
                    '8주',
                    '12주',
                    '6개월',
                    '1년',
                ],
                'is_required' => true,
                'order' => 2,
            ],
            [
                'question_text' => '선호하는 다이어트 방식',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '식단 조절 중심',
                    '운동 중심',
                    '식단과 운동 병행',
                    '간헐적 단식',
                    '저탄수화물',
                ],
                'is_required' => false,
                'order' => 3,
            ],
            [
                'question_text' => '하루 가능 운동 시간',
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

        $this->command->info('Step 2 (목표 설정): ' . count($questions) . ' questions created');
    }

    /**
     * Step 3: 생활 패턴 질문 생성
     * DietPlanService에서 사용하는 키: 운동 경험, 하루 식사 횟수
     */
    private function createLifestyleQuestions(Survey $survey): void
    {
        $questions = [
            [
                'question_text' => '운동 경험',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '초보',
                    '중급',
                    '고급',
                ],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '하루 식사 횟수',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '1회',
                    '2회',
                    '3회',
                    '4회 이상',
                ],
                'is_required' => true,
                'order' => 2,
            ],
            [
                'question_text' => '평균 수면 시간',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '5시간 미만',
                    '5-6시간',
                    '6-7시간',
                    '7-8시간',
                    '8시간 이상',
                ],
                'is_required' => false,
                'order' => 3,
            ],
            [
                'question_text' => '직업/활동 유형',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '사무직 (주로 앉아서 근무)',
                    '서비스직 (주로 서서 근무)',
                    '육체 노동직',
                    '학생',
                    '주부',
                    '프리랜서/재택근무',
                ],
                'is_required' => false,
                'order' => 4,
            ],
            [
                'question_text' => '스트레스 수준',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '매우 낮음',
                    '낮음',
                    '보통',
                    '높음',
                    '매우 높음',
                ],
                'is_required' => false,
                'order' => 5,
            ],
            [
                'question_text' => '하루 물 섭취량',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '500ml 미만',
                    '500ml-1L',
                    '1L-1.5L',
                    '1.5L-2L',
                    '2L 이상',
                ],
                'is_required' => false,
                'order' => 6,
            ],
        ];

        foreach ($questions as $questionData) {
            SurveyQuestion::create([
                'survey_id' => $survey->id,
                'step' => SurveyStep::LIFESTYLE,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }

        $this->command->info('Step 3 (생활 패턴): ' . count($questions) . ' questions created');
    }

    /**
     * Step 4: 건강 & 선호도 질문 생성
     * DietPlanService에서 사용하는 키: 선호하는 음식 종류, 싫어하는 음식 재료, 식이 제한
     */
    private function createHealthPreferenceQuestions(Survey $survey): void
    {
        $questions = [
            [
                'question_text' => '선호하는 음식 종류',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => [
                    '한식',
                    '양식',
                    '중식',
                    '일식',
                    '샐러드',
                    '육류',
                    '해산물',
                    '채소',
                ],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '싫어하는 음식 재료',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => [
                    '없음',
                    '매운 음식',
                    '기름진 음식',
                    '날것 (회, 육회 등)',
                    '내장류',
                    '해산물',
                    '유제품',
                    '특정 채소',
                ],
                'is_required' => false,
                'order' => 2,
            ],
            [
                'question_text' => '식이 제한',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => [
                    '없음',
                    '유제품 불가',
                    '해산물 알레르기',
                    '견과류 알레르기',
                    '계란 알레르기',
                    '글루텐 불가',
                    '채식주의',
                    '할랄',
                ],
                'is_required' => true,
                'order' => 3,
            ],
            [
                'question_text' => '현재 건강 상태',
                'question_type' => QuestionType::MULTI_SELECT,
                'options' => [
                    '양호',
                    '당뇨병',
                    '고혈압',
                    '고지혈증',
                    '갑상선 질환',
                    '소화기 질환',
                    '심혈관 질환',
                    '기타',
                ],
                'is_required' => false,
                'order' => 4,
            ],
            [
                'question_text' => '외식 빈도',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '거의 안 함 (주 0-1회)',
                    '가끔 (주 2-3회)',
                    '자주 (주 4-5회)',
                    '매우 자주 (주 6회 이상)',
                ],
                'is_required' => false,
                'order' => 5,
            ],
        ];

        foreach ($questions as $questionData) {
            SurveyQuestion::create([
                'survey_id' => $survey->id,
                'step' => SurveyStep::HEALTH_PREFERENCE,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }

        $this->command->info('Step 4 (건강 & 선호도): ' . count($questions) . ' questions created');
    }

    /**
     * Step 5: 추가 정보 질문 생성
     * DietPlanService에서 사용하는 키: 조리 가능 여부
     */
    private function createAdditionalQuestions(Survey $survey): void
    {
        $questions = [
            [
                'question_text' => '조리 가능 여부',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '직접 조리',
                    '간단한 조리만 가능',
                    '조리 불가 (배달/외식)',
                ],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '식사 준비 시간',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '10분 이내',
                    '10-20분',
                    '20-30분',
                    '30분 이상',
                ],
                'is_required' => false,
                'order' => 2,
            ],
            [
                'question_text' => '식비 예산 (1일)',
                'question_type' => QuestionType::SELECT,
                'options' => [
                    '1만원 미만',
                    '1-2만원',
                    '2-3만원',
                    '3만원 이상',
                ],
                'is_required' => false,
                'order' => 3,
            ],
            [
                'question_text' => '추가 요청사항',
                'question_type' => QuestionType::TEXT,
                'options' => null,
                'is_required' => false,
                'order' => 4,
            ],
        ];

        foreach ($questions as $questionData) {
            SurveyQuestion::create([
                'survey_id' => $survey->id,
                'step' => SurveyStep::ADDITIONAL,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => $questionData['options'],
                'is_required' => $questionData['is_required'],
                'order' => $questionData['order'],
            ]);
        }

        $this->command->info('Step 5 (추가 정보): ' . count($questions) . ' questions created');
    }
}
