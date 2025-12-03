# 설문 시스템 구조

## 개요

맞춤 다이어트 플랜 생성을 위한 5단계 온보딩 설문 시스템입니다.

## 설문 단계 (Survey Steps)

### Step 1: 기본 정보 (BASIC_INFO) ✅ 구현 완료

사용자의 신체 정보와 현재 상태를 파악합니다.

**질문 목록:**

1. **성별** (필수)
   - 유형: SELECT
   - 옵션: 남성, 여성

2. **생년월일** (필수)
   - 유형: NUMBER
   - 형식: YYYY (예: 1990)

3. **키** (필수)
   - 유형: NUMBER
   - 단위: cm
   - 예시: 175

4. **현재 체중** (필수)
   - 유형: NUMBER
   - 단위: kg
   - 예시: 80

5. **목표 체중** (필수)
   - 유형: NUMBER
   - 단위: kg
   - 예시: 70

6. **평소 활동량** (필수)
   - 유형: SELECT
   - 옵션:
     - 매우 낮음 (거의 운동 안함, 주로 앉아서 생활)
     - 낮음 (주 1-2회 가벼운 운동)
     - 보통 (주 3-4회 규칙적인 운동)
     - 높음 (주 5-6회 강도 높은 운동)
     - 매우 높음 (매일 고강도 운동, 육체노동)

7. **현재 체형 인식** (선택)
   - 유형: SELECT
   - 옵션: 매우 마른 편, 마른 편, 표준, 통통한 편, 비만

8. **다이어트 경험** (선택)
   - 유형: SELECT
   - 옵션: 없음, 1-2회, 3-5회, 5회 이상

### Step 2: 목표 설정 (GOAL_SETTING) ⬜ 미구현

다이어트 목표와 목표 달성 기간을 설정합니다.

**계획된 질문:**
- 목표 달성 기간
- 주요 다이어트 목표
- 선호하는 다이어트 방식
- 하루 운동 가능 시간

### Step 3: 생활 패턴 (LIFESTYLE) ⬜ 미구현

일상 생활 패턴과 식습관을 파악합니다.

**계획된 질문:**
- 기상/취침 시간
- 식사 횟수 및 시간
- 야식 빈도
- 직업 유형
- 스트레스 수준

### Step 4: 건강 & 선호도 (HEALTH_PREFERENCE) ⬜ 미구현

건강 상태와 음식 선호도를 확인합니다.

**계획된 질문:**
- 알레르기 및 제한 음식
- 선호하는 음식 종류
- 기피 음식
- 건강 상태 (당뇨, 고혈압 등)
- 현재 복용 중인 약

### Step 5: 추가 정보 (ADDITIONAL) ⬜ 미구현

더 정확한 플랜을 위한 추가 정보를 수집합니다.

**계획된 질문:**
- 특별한 건강 목표
- 이전 다이어트 실패 원인
- 동기부여 요소
- 식단 제한 가능성
- 지원 시스템 (가족, 친구)

## 데이터 구조

### Survey (설문)

```php
[
    'id' => 1,
    'title' => '맞춤 다이어트 플랜 생성 설문',
    'description' => '당신에게 딱 맞는 다이어트 플랜을 만들어드리기 위한 5단계 설문입니다.',
    'is_active' => true,
    'created_at' => '2025-12-03T14:00:00Z',
    'updated_at' => '2025-12-03T14:00:00Z',
]
```

### SurveyQuestion (질문)

```php
[
    'id' => 1,
    'survey_id' => 1,
    'step' => 1, // SurveyStep enum
    'question_text' => '성별을 선택해주세요',
    'question_type' => 'select', // QuestionType enum
    'options' => ['남성', '여성'],
    'is_required' => true,
    'order' => 1,
    'created_at' => '2025-12-03T14:00:00Z',
    'updated_at' => '2025-12-03T14:00:00Z',
]
```

### UserSurveyResponse (사용자 응답)

```php
[
    'id' => 1,
    'user_id' => 1,
    'survey_id' => 1,
    'survey_question_id' => 1,
    'answer' => ['value' => '남성'], // JSON 형식
    'answered_at' => '2025-12-03T14:30:00Z',
    'created_at' => '2025-12-03T14:30:00Z',
    'updated_at' => '2025-12-03T14:30:00Z',
]
```

## API 사용 예시

### 1. 활성 설문 조회

```bash
GET /api/surveys
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "survey": {
      "id": 1,
      "title": "맞춤 다이어트 플랜 생성 설문",
      "description": "...",
      "total_questions": 8
    }
  }
}
```

### 2. Step 1 질문 조회

```bash
GET /api/surveys/1/questions?step=1
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "step": 1,
    "step_name": "기본 정보",
    "description": "신체 정보와 기본 프로필을 입력합니다",
    "questions": [
      {
        "id": 1,
        "question_text": "성별을 선택해주세요",
        "question_type": "select",
        "options": ["남성", "여성"],
        "is_required": true,
        "order": 1
      },
      // ... 7개 더
    ]
  }
}
```

### 3. 기본 정보 답변 제출

```bash
POST /api/surveys/1/answers
Authorization: Bearer {token}
Content-Type: application/json

{
  "answers": {
    "1": "남성",
    "2": 1990,
    "3": 175,
    "4": 80,
    "5": 70,
    "6": "보통 (주 3-4회 규칙적인 운동)"
  }
}

Response:
{
  "success": true,
  "message": "답변이 저장되었습니다.",
  "data": {
    "saved_count": 6,
    "answers": [...],
    "progress": {
      "total_questions": 8,
      "answered_questions": 6,
      "percentage": 75,
      "is_complete": false
    }
  }
}
```

### 4. 진행률 확인

```bash
GET /api/surveys/1/progress
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "total_questions": 8,
    "answered_questions": 6,
    "percentage": 75,
    "is_complete": false,
    "step_progress": [
      {
        "step": 1,
        "step_name": "기본 정보",
        "total": 8,
        "answered": 6,
        "is_complete": false
      },
      // ... 다른 단계들
    ]
  }
}
```

### 5. 설문 요약 조회

```bash
GET /api/surveys/1/summary
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "survey": {
      "id": 1,
      "title": "맞춤 다이어트 플랜 생성 설문",
      "description": "..."
    },
    "progress": {...},
    "next_step": {
      "step": 1,
      "step_name": "기본 정보",
      "description": "신체 정보와 기본 프로필을 입력합니다"
    },
    "total_responses": 6
  }
}
```

## 설문 데이터 시딩

### 개발 환경 설정

```bash
# 마이그레이션 실행
php artisan migrate

# 설문 데이터 시드
php artisan db:seed --class=SurveySeeder

# 또는 전체 데이터베이스 시드
php artisan db:seed
```

### 프로덕션 환경

프로덕션 환경에서는 SurveySeeder를 별도로 실행하여 설문 데이터만 시드합니다:

```bash
php artisan db:seed --class=SurveySeeder --force
```

## 검증 규칙

### QuestionType별 검증

- **TEXT**: `string|max:1000`
- **NUMBER**: `numeric`
- **SELECT**: 답변이 options 배열에 포함되어야 함
- **MULTI_SELECT**: 답변 배열의 모든 값이 options에 포함되어야 함

### 필수 질문

`is_required: true`인 질문은 반드시 답변해야 합니다.

### 선택 질문

`is_required: false`인 질문은 건너뛸 수 있습니다.

## 향후 개발 계획

1. **Step 2-5 질문 추가** (Epic 1.2의 나머지 Sub-Epics)
2. **답변 기반 AI 플랜 생성** (Epic 1.3)
3. **설문 결과 시각화** 대시보드
4. **설문 응답 수정** 기능 개선
5. **다국어 지원** (영어, 일본어 등)

## 테스트

### 기본 정보 설문 테스트

```bash
# 모든 설문 테스트 실행
php artisan test --filter=SurveyTest

# 기본 정보 설문만 테스트
php artisan test --filter=BasicInfoSurveyTest

# 특정 테스트 케이스
php artisan test --filter=test_can_submit_complete_basic_info
```

## 문의 및 개선 제안

설문 구조나 질문 내용에 대한 개선 제안은 이슈로 등록해주세요.
