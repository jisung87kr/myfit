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

### Step 2: 목표 설정 (GOAL_SETTING) ✅ 구현 완료

다이어트 목표와 목표 달성 기간을 설정합니다.

**질문 목록:**

1. **목표 달성 희망 기간** (필수)
   - 유형: SELECT
   - 옵션: 1개월 이내, 1-3개월, 3-6개월, 6개월-1년, 1년 이상, 기간 상관없이 천천히

2. **주요 다이어트 목표** (필수)
   - 유형: MULTI_SELECT
   - 옵션: 체중 감량, 체지방 감소, 근육량 증가, 건강 개선, 체형 관리, 자신감 향상

3. **선호하는 다이어트 방식** (필수)
   - 유형: SELECT
   - 옵션: 식단 조절 중심, 운동 중심, 식단과 운동 병행, 간헐적 단식, 저탄수화물 (키토), 전문가 상담 후 결정

4. **하루 운동 가능 시간** (필수)
   - 유형: SELECT
   - 옵션: 운동 불가, 15-30분, 30분-1시간, 1-2시간, 2시간 이상

5. **선호하는 운동 유형** (선택)
   - 유형: MULTI_SELECT
   - 옵션: 유산소 (달리기, 자전거 등), 근력 운동 (웨이트), 홈트레이닝, 요가/필라테스, 수영, 구기 종목, 등산/산책, 운동 안함

6. **다이어트 성공 중요 요소** (선택)
   - 유형: SELECT
   - 옵션: 꾸준한 실천, 철저한 식단 관리, 규칙적인 운동, 충분한 휴식과 수면, 스트레스 관리, 전문가의 도움

### Step 3: 생활 패턴 (LIFESTYLE) ✅ 구현 완료

일상 생활 패턴과 식습관을 파악합니다.

**질문 목록:**

1. **평균 수면 시간** (필수)
   - 유형: SELECT
   - 옵션: 5시간 미만, 5-6시간, 6-7시간, 7-8시간, 8시간 이상

2. **하루 평균 식사 횟수** (필수)
   - 유형: SELECT
   - 옵션: 1회, 2회, 3회, 4회 이상, 불규칙

3. **주로 식사하는 시간대** (필수)
   - 유형: MULTI_SELECT
   - 옵션: 아침 (06:00-09:00), 오전 간식 (09:00-12:00), 점심 (12:00-14:00), 오후 간식 (14:00-18:00), 저녁 (18:00-21:00), 야식 (21:00 이후)

4. **야식 빈도** (필수)
   - 유형: SELECT
   - 옵션: 거의 안 먹음, 주 1-2회, 주 3-4회, 주 5-6회, 거의 매일

5. **현재 직업/활동 유형** (필수)
   - 유형: SELECT
   - 옵션: 사무직 (주로 앉아서 근무), 서비스직 (주로 서서 근무), 육체 노동직, 학생, 주부, 프리랜서/재택근무, 무직/구직 중

6. **평소 스트레스 수준** (필수)
   - 유형: SELECT
   - 옵션: 매우 낮음, 낮음, 보통, 높음, 매우 높음

7. **하루 물 섭취량** (선택)
   - 유형: SELECT
   - 옵션: 500ml 미만, 500ml-1L, 1L-1.5L, 1.5L-2L, 2L 이상

8. **음주 빈도** (선택)
   - 유형: SELECT
   - 옵션: 거의 안 함, 월 1-2회, 주 1-2회, 주 3-4회, 거의 매일

9. **흡연 여부** (선택)
   - 유형: SELECT
   - 옵션: 비흡연, 과거 흡연 (현재 금연), 하루 반 갑 미만, 하루 반 갑-1갑, 하루 1갑 이상

### Step 4: 건강 & 선호도 (HEALTH_PREFERENCE) ✅ 구현 완료

건강 상태와 음식 선호도를 확인합니다.

**질문 목록:**

1. **알레르기/제한 음식** (필수)
   - 유형: MULTI_SELECT
   - 옵션: 없음, 유제품, 해산물, 견과류, 계란, 밀가루 (글루텐), 콩류, 기타

2. **선호하는 음식 종류** (필수)
   - 유형: MULTI_SELECT
   - 옵션: 한식, 양식, 중식, 일식, 샐러드, 과일, 육류, 생선, 채소, 곡물/잡곡

3. **기피하는 음식** (선택)
   - 유형: MULTI_SELECT
   - 옵션: 없음, 매운 음식, 기름진 음식, 날것, 내장류, 특정 채소, 유제품, 해산물

4. **건강 상태** (필수)
   - 유형: MULTI_SELECT
   - 옵션: 없음, 당뇨병, 고혈압, 고지혈증, 갑상선 질환, 소화기 질환, 심혈관 질환, 관절염, 기타

5. **현재 복용 중인 약/영양제** (선택)
   - 유형: SELECT
   - 옵션: 없음, 처방약 복용 중, 영양제만 복용 중, 처방약과 영양제 모두 복용

6. **식단 제한 (종교/신념)** (선택)
   - 유형: SELECT
   - 옵션: 없음, 채식주의 (비건), 채식주의 (락토/오보), 할랄, 코셔, 기타

7. **외식 빈도** (필수)
   - 유형: SELECT
   - 옵션: 거의 안 함 (주 0-1회), 가끔 (주 2-3회), 자주 (주 4-5회), 매우 자주 (주 6회 이상), 거의 매끼

8. **간식 섭취 시간** (선택)
   - 유형: MULTI_SELECT
   - 옵션: 먹지 않음, 오전, 오후, 저녁 후, 불규칙적

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

### 6. 설문 상태 조회

```bash
GET /api/surveys/1/status
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "status": "in_progress",  // not_started, in_progress, completed
    "total_questions": 31,
    "answered_questions": 15,
    "percentage": 48
  }
}
```

### 7. 특정 답변 삭제

```bash
DELETE /api/surveys/1/answers/5
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "답변이 삭제되었습니다.",
  "data": null
}
```

### 8. 특정 단계 답변 삭제

```bash
DELETE /api/surveys/1/steps/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "confirm": true
}

Response:
{
  "success": true,
  "message": "기본 정보 단계의 답변이 삭제되었습니다.",
  "data": {
    "step": 1,
    "deleted_count": 8
  }
}
```

### 9. 설문 전체 초기화

```bash
DELETE /api/surveys/1/reset
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "설문이 초기화되었습니다.",
  "data": {
    "deleted_count": 15
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

## 설문 관리 기능

### 답변 삭제

사용자는 제출한 답변을 개별적으로 삭제할 수 있습니다. 답변 삭제 시 자동으로 진행률이 업데이트됩니다.

- 엔드포인트: `DELETE /api/surveys/{survey}/answers/{question}`
- 자신의 답변만 삭제 가능
- 삭제 후 진행률 자동 업데이트

### 단계별 답변 삭제

특정 단계의 모든 답변을 한번에 삭제할 수 있습니다. 실수 방지를 위해 `confirm` 파라미터가 필요합니다.

- 엔드포인트: `DELETE /api/surveys/{survey}/steps/{step}`
- `confirm: true` 필수
- 해당 단계의 모든 답변 삭제
- 다른 단계의 답변은 유지

### 설문 초기화

모든 답변을 삭제하고 설문을 처음부터 다시 시작할 수 있습니다.

- 엔드포인트: `DELETE /api/surveys/{survey}/reset`
- 모든 단계의 답변 삭제
- 진행률 0%로 초기화
- 다른 사용자의 답변에는 영향 없음

### 설문 상태 확인

현재 설문의 진행 상태를 간단하게 확인할 수 있습니다.

- 엔드포인트: `GET /api/surveys/{survey}/status`
- 상태 종류:
  - `not_started`: 아직 답변 시작 전
  - `in_progress`: 일부 답변 완료
  - `completed`: 모든 질문 답변 완료
- 전체 질문 수, 답변한 질문 수, 진행률 포함

## 향후 개발 계획

1. **Step 5 질문 추가** (Epic 1.2.5 - Additional Info)
2. **설문 결과 최종 제출** (Epic 1.2.7)
3. **답변 기반 AI 플랜 생성** (Epic 1.3)
4. **설문 결과 시각화** 대시보드
5. **다국어 지원** (영어, 일본어 등)

## 테스트

### 설문 테스트 실행

```bash
# 모든 설문 테스트 실행
php artisan test --filter=SurveyTest

# 기본 정보 설문 테스트
php artisan test --filter=BasicInfoSurveyTest

# 목표 설정 설문 테스트
php artisan test --filter=GoalSettingSurveyTest

# 생활 패턴 설문 테스트
php artisan test --filter=LifestyleSurveyTest

# 건강 & 선호도 설문 테스트
php artisan test --filter=HealthPreferenceSurveyTest

# 설문 관리 기능 테스트
php artisan test --filter=SurveyManagementTest

# 특정 테스트 케이스
php artisan test --filter=test_can_submit_complete_basic_info
```

### 테스트 커버리지

- **SurveyTest**: 18 tests - 설문 인프라 및 핵심 기능
- **BasicInfoSurveyTest**: 12 tests - Step 1 기본 정보
- **GoalSettingSurveyTest**: 13 tests - Step 2 목표 설정
- **LifestyleSurveyTest**: 13 tests - Step 3 생활 패턴
- **HealthPreferenceSurveyTest**: 10 tests - Step 4 건강 & 선호도
- **SurveyManagementTest**: 15 tests - 설문 관리 기능

**총 81개 테스트** - 설문 시스템의 모든 주요 기능 커버

## 문의 및 개선 제안

설문 구조나 질문 내용에 대한 개선 제안은 이슈로 등록해주세요.
