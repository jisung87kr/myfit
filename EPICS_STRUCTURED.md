# MyFit 프로젝트 에픽 구조

## 프로젝트 개요

**주제**: AI 기반 맞춤형 다이어트 플랜 웹서비스
**타겟**: 다이어트가 익숙하지 않은 사용자
**핵심 가치**: 간단한 설문 → AI 플랜 생성 → 쉬운 실행 및 추적

---

# Phase 1: MVP (Minimum Viable Product)

> **목표**: 사용자가 설문을 완료하고 AI 플랜을 받아 기본적인 기록을 할 수 있는 시스템
> **기간**: 4-6주
> **출시 기준**: 회원가입 → 설문 → AI 플랜 생성 → 일일 기록

---

## Epic 1.1: 사용자 인증 시스템

**Phase**: 1 (MVP)
**Sprint**: 1 (Week 2-3)
**우선순위**: CRITICAL

### 목적
사용자가 안전하게 가입하고 로그인할 수 있는 기반 구축

---

### Sub-Epic 1.1.1: 이메일 회원가입/로그인

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 이메일과 비밀번호로 회원가입할 수 있다
- [ ] 사용자로서, 이메일 중복 확인을 받을 수 있다
- [ ] 사용자로서, 비밀번호 강도를 확인받을 수 있다
- [ ] 사용자로서, 로그인하여 API 토큰을 받을 수 있다
- [ ] 사용자로서, 로그아웃할 수 있다

#### API Endpoints
```
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/user
```

#### 데이터 모델
```
users
- id
- name
- email (unique)
- email_verified_at
- password
- remember_token
- created_at
- updated_at
```

#### 기술 스택
- Laravel Sanctum ✓
- Validation Rules
- Password Hashing

#### 완료 조건
- [ ] 회원가입 API 정상 동작
- [ ] 로그인 시 토큰 발급
- [ ] 토큰으로 인증된 요청 가능
- [ ] 테스트 커버리지 80% 이상

---

### Sub-Epic 1.1.2: 프로필 관리

**Story Points**: 3

#### User Stories
- [ ] 사용자로서, 내 프로필 정보를 조회할 수 있다
- [ ] 사용자로서, 이름을 수정할 수 있다
- [ ] 사용자로서, 비밀번호를 변경할 수 있다
- [ ] 사용자로서, 프로필 사진을 업로드할 수 있다

#### API Endpoints
```
GET    /api/user/profile
PUT    /api/user/profile
POST   /api/user/profile/photo
PUT    /api/user/password
```

#### 데이터 모델 (확장)
```
users
+ profile_photo_path
+ phone (nullable)
```

#### 완료 조건
- [ ] 프로필 조회/수정 API 동작
- [ ] 비밀번호 변경 시 기존 비밀번호 확인
- [ ] 이미지 업로드 및 저장

---

### Sub-Epic 1.1.3: 소셜 로그인

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, Google 계정으로 간편 가입/로그인할 수 있다
- [ ] 사용자로서, Kakao 계정으로 간편 가입/로그인할 수 있다
- [ ] 사용자로서, 소셜 계정 연동을 해제할 수 있다

#### API Endpoints
```
GET    /api/auth/google/redirect
GET    /api/auth/google/callback
GET    /api/auth/kakao/redirect
GET    /api/auth/kakao/callback
DELETE /api/auth/social/{provider}
```

#### 데이터 모델
```
social_accounts
- id
- user_id
- provider (google, kakao)
- provider_id
- token
- created_at
- updated_at
```

#### 기술 스택
- Laravel Socialite
- Google OAuth2
- Kakao OAuth2

#### 완료 조건
- [ ] Google 로그인 동작
- [ ] Kakao 로그인 동작
- [ ] 기존 이메일과 소셜 계정 연동

---

### Sub-Epic 1.1.4: 비밀번호 찾기/재설정

**Story Points**: 3

#### User Stories
- [ ] 사용자로서, 비밀번호를 잊었을 때 재설정 이메일을 받을 수 있다
- [ ] 사용자로서, 이메일 링크를 통해 새 비밀번호를 설정할 수 있다
- [ ] 사용자로서, 재설정 링크의 유효기간을 확인할 수 있다

#### API Endpoints
```
POST   /api/password/email
POST   /api/password/reset
```

#### 완료 조건
- [ ] 재설정 이메일 발송
- [ ] 토큰 유효성 검증
- [ ] 비밀번호 재설정 완료

---

## Epic 1.2: 온보딩 & 설문 시스템

**Phase**: 1 (MVP)
**Sprint**: 2 (Week 3-4)
**우선순위**: CRITICAL

### 목적
사용자 정보를 수집하여 AI 플랜 생성의 기초 데이터 확보

---

### Sub-Epic 1.2.1: 설문 인프라 구축

**Story Points**: 5

#### User Stories
- [ ] 개발자로서, 설문 템플릿을 데이터베이스에 정의할 수 있다
- [ ] 개발자로서, 질문 타입(선택형, 입력형 등)을 관리할 수 있다
- [ ] 사용자로서, 설문을 단계별로 볼 수 있다
- [ ] 사용자로서, 진행률을 확인할 수 있다

#### API Endpoints
```
GET    /api/surveys
GET    /api/surveys/{id}/questions
GET    /api/surveys/progress
```

#### 데이터 모델
```
surveys
- id
- title
- description
- is_active
- created_at
- updated_at

survey_questions
- id
- survey_id
- step (1-5)
- question_text
- question_type (text, number, select, multi_select)
- options (json)
- is_required
- order
- created_at
- updated_at

user_survey_responses
- id
- user_id
- survey_id
- question_id
- answer (json)
- created_at
- updated_at
```

#### 완료 조건
- [ ] 설문 구조 설계 완료
- [ ] 설문 조회 API 동작
- [ ] 진행률 계산 로직 구현

---

### Sub-Epic 1.2.2: 기본 정보 설문 (Step 1)

**Story Points**: 3

#### 질문 항목 (5문항)
1. 성별 (남성/여성)
2. 나이 (숫자 입력)
3. 키 (cm)
4. 현재 체중 (kg)
5. 목표 체중 (kg)

#### User Stories
- [ ] 사용자로서, 기본 정보를 입력할 수 있다
- [ ] 사용자로서, 입력값의 유효성 검증을 받을 수 있다
- [ ] 사용자로서, BMI를 자동으로 계산받을 수 있다

#### API Endpoints
```
POST   /api/surveys/responses/basic-info
PUT    /api/surveys/responses/basic-info
```

#### 완료 조건
- [ ] 5개 질문 데이터 등록
- [ ] 응답 저장 API 동작
- [ ] BMI 계산 로직 구현

---

### Sub-Epic 1.2.3: 목표 설정 설문 (Step 2)

**Story Points**: 2

#### 질문 항목 (3문항)
1. 희망 감량 기간 (4주/8주/12주/16주)
2. 주요 목표 (체중 감량/체지방 감소/근육량 증가/건강 유지)
3. 운동 경험 (없음/초보/중급/상급)

#### User Stories
- [ ] 사용자로서, 현실적인 목표를 설정할 수 있다
- [ ] 사용자로서, 목표 기간에 따른 주간 감량 속도를 확인할 수 있다

#### API Endpoints
```
POST   /api/surveys/responses/goals
PUT    /api/surveys/responses/goals
```

#### 완료 조건
- [ ] 3개 질문 데이터 등록
- [ ] 주간 감량 목표 계산 (-0.5kg ~ -1kg/week)

---

### Sub-Epic 1.2.4: 생활 패턴 설문 (Step 3)

**Story Points**: 3

#### 질문 항목 (5문항)
1. 일일 활동량 (좌식/가벼운 활동/보통/활동적/매우 활동적)
2. 평균 수면 시간 (시간)
3. 하루 식사 횟수 (2회/3회/4회 이상)
4. 선호하는 식사 시간 (아침/점심/저녁 각각 시간대)
5. 간식 섭취 빈도 (전혀 안함/가끔/자주)

#### User Stories
- [ ] 사용자로서, 내 생활 패턴을 입력할 수 있다
- [ ] 사용자로서, 활동량에 따른 칼로리 소모를 확인할 수 있다

#### API Endpoints
```
POST   /api/surveys/responses/lifestyle
PUT    /api/surveys/responses/lifestyle
```

#### 완료 조건
- [ ] 5개 질문 데이터 등록
- [ ] TDEE 계산 기초 데이터 수집

---

### Sub-Epic 1.2.5: 건강 & 선호도 설문 (Step 4-5)

**Story Points**: 3

#### 질문 항목 (8문항)
**건강 상태 (4문항)**
1. 기저 질환 (당뇨/고혈압/갑상선/없음)
2. 복용 중인 약물 (있음/없음)
3. 알레르기 (있음/없음 + 항목)
4. 임신/수유 여부 (해당시)

**식품 선호도 (4문항)**
1. 선호하는 음식 종류 (한식/양식/일식/중식 등)
2. 싫어하는 음식 재료 (다중 선택)
3. 식이 제한 (채식/할랄/글루텐프리/없음)
4. 조리 가능 여부 (직접 조리/간편식 선호/배달 주문)

#### User Stories
- [ ] 사용자로서, 건강 상태를 입력하여 안전한 플랜을 받을 수 있다
- [ ] 사용자로서, 내가 좋아하는 음식 위주의 플랜을 받을 수 있다
- [ ] 사용자로서, 알레르기가 있는 음식을 제외할 수 있다

#### API Endpoints
```
POST   /api/surveys/responses/health-preferences
PUT    /api/surveys/responses/health-preferences
POST   /api/surveys/complete
```

#### 완료 조건
- [ ] 8개 질문 데이터 등록
- [ ] 설문 완료 처리
- [ ] AI 플랜 생성 트리거

---

### Sub-Epic 1.2.6: 설문 관리 기능

**Story Points**: 2

#### User Stories
- [ ] 사용자로서, 설문 중간에 저장하고 나중에 이어할 수 있다
- [ ] 사용자로서, 이전 단계로 돌아가 수정할 수 있다
- [ ] 사용자로서, 완료 후에도 설문을 수정할 수 있다
- [ ] 사용자로서, 수정 시 플랜이 자동으로 재생성됨을 알 수 있다

#### API Endpoints
```
GET    /api/surveys/responses/saved
PUT    /api/surveys/responses/{id}
DELETE /api/surveys/responses/{id}
```

#### 완료 조건
- [ ] 임시 저장 기능
- [ ] 수정 후 플랜 재생성 로직

---

## Epic 1.3: AI 다이어트 플랜 생성

**Phase**: 1 (MVP)
**Sprint**: 3 (Week 5-6)
**우선순위**: CRITICAL

### 목적
설문 데이터를 기반으로 AI가 개인 맞춤형 다이어트 플랜 자동 생성

---

### Sub-Epic 1.3.1: 칼로리 계산 엔진

**Story Points**: 5

#### User Stories
- [ ] 시스템으로서, 사용자의 BMR을 계산할 수 있다
- [ ] 시스템으로서, 활동량에 따른 TDEE를 계산할 수 있다
- [ ] 시스템으로서, 목표에 맞는 일일 목표 칼로리를 산출할 수 있다
- [ ] 시스템으로서, 영양소 비율(탄단지)을 계산할 수 있다

#### 계산 공식
```php
// BMR (Harris-Benedict)
남성: 88.362 + (13.397 × 체중kg) + (4.799 × 키cm) - (5.677 × 나이)
여성: 447.593 + (9.247 × 체중kg) + (3.098 × 키cm) - (4.330 × 나이)

// TDEE
TDEE = BMR × 활동계수
- 좌식: 1.2
- 가벼운 활동: 1.375
- 보통: 1.55
- 활동적: 1.725
- 매우 활동적: 1.9

// 목표 칼로리
감량: TDEE - 500kcal (주 0.5kg)
감량: TDEE - 1000kcal (주 1kg) - 단, 1200kcal 이상 유지
유지: TDEE
증량: TDEE + 300~500kcal
```

#### API Endpoints
```
POST   /api/calculations/bmr
POST   /api/calculations/tdee
POST   /api/calculations/target-calories
```

#### 데이터 모델
```
user_calculations
- id
- user_id
- bmr
- tdee
- target_calories
- target_protein_g
- target_carbs_g
- target_fat_g
- calculated_at
```

#### 완료 조건
- [ ] BMR 계산 정확도 검증
- [ ] TDEE 계산 구현
- [ ] 목표 칼로리 산출
- [ ] 영양소 비율 계산 (탄수화물 40%, 단백질 30%, 지방 30%)

---

### Sub-Epic 1.3.2: 음식 & 운동 데이터베이스

**Story Points**: 8

#### User Stories
- [ ] 시스템으로서, 기본 음식 100가지의 영양 정보를 가지고 있다
- [ ] 시스템으로서, 기본 운동 50가지의 칼로리 소모 정보를 가지고 있다
- [ ] 관리자로서, 음식을 추가하고 수정할 수 있다
- [ ] 관리자로서, 운동을 추가하고 수정할 수 있다

#### 데이터 모델
```
foods
- id
- name
- name_en (nullable)
- category (곡류/단백질/채소/과일/유제품/견과류/음료)
- serving_size (1인분 g)
- calories (kcal)
- protein_g
- carbs_g
- fat_g
- fiber_g (nullable)
- sodium_mg (nullable)
- image_url (nullable)
- created_at
- updated_at

exercises
- id
- name
- category (유산소/근력/스트레칭/스포츠)
- intensity (낮음/보통/높음)
- met_value (대사당량)
- calories_per_hour_per_kg
- description (nullable)
- video_url (nullable)
- created_at
- updated_at
```

#### 초기 데이터
**음식 100가지 (카테고리별)**
- 곡류: 밥, 빵, 면 등 (20종)
- 단백질: 고기, 생선, 계란, 두부 등 (25종)
- 채소: 각종 채소 (20종)
- 과일: 각종 과일 (15종)
- 유제품: 우유, 요거트 등 (10종)
- 견과류/기타: (10종)

**운동 50가지**
- 유산소: 걷기, 조깅, 사이클링 등 (15종)
- 근력: 푸쉬업, 스쿼트, 플랭크 등 (20종)
- 스트레칭: 요가, 스트레칭 동작 (10종)
- 스포츠: 수영, 테니스 등 (5종)

#### API Endpoints
```
GET    /api/foods
GET    /api/foods/search?q=
GET    /api/foods/{id}
POST   /api/admin/foods
PUT    /api/admin/foods/{id}

GET    /api/exercises
GET    /api/exercises/search?q=
GET    /api/exercises/{id}
POST   /api/admin/exercises
PUT    /api/admin/exercises/{id}
```

#### 완료 조건
- [ ] 음식 100개 데이터 등록
- [ ] 운동 50개 데이터 등록
- [ ] 검색 API 구현
- [ ] 관리자 CRUD 구현

---

### Sub-Epic 1.3.3: AI 플랜 생성 (GPT API)

**Story Points**: 13

#### User Stories
- [ ] 사용자로서, 설문 완료 후 자동으로 7일치 플랜을 받을 수 있다
- [ ] 사용자로서, AI가 생성한 플랜의 근거를 이해할 수 있다
- [ ] 사용자로서, 하루 3끼 + 간식 식단을 받을 수 있다
- [ ] 사용자로서, 주 3-5회 운동 플랜을 받을 수 있다

#### AI 프롬프트 설계
```
당신은 영양학 전문가입니다. 다음 사용자 정보를 바탕으로
7일간의 맞춤형 다이어트 플랜을 작성해주세요.

[사용자 정보]
- 성별: {gender}
- 나이: {age}
- 현재 체중/목표 체중: {current_weight}kg / {target_weight}kg
- BMI: {bmi}
- 목표 칼로리: {target_calories}kcal/day
- 활동량: {activity_level}
- 운동 경험: {exercise_level}
- 선호 음식: {preferred_foods}
- 제외 음식: {excluded_foods}
- 식이 제한: {dietary_restrictions}
- 조리 가능: {can_cook}

[플랜 요구사항]
1. 7일간의 일별 식단 (아침, 점심, 저녁, 간식)
2. 각 끼니별 칼로리 및 영양소
3. 주 {exercise_frequency}회 운동 스케줄
4. 실천 가능한 팁

[응답 형식: JSON]
{
  "summary": "플랜 요약 및 근거",
  "daily_plans": [
    {
      "day": 1,
      "meals": {
        "breakfast": {...},
        "lunch": {...},
        "dinner": {...},
        "snack": {...}
      },
      "exercise": {...},
      "total_calories": 1500,
      "tips": "..."
    }
  ]
}
```

#### 데이터 모델
```
diet_plans
- id
- user_id
- survey_response_id
- status (generating, active, completed, archived)
- start_date
- end_date (7일 후)
- target_calories_per_day
- ai_summary (AI가 생성한 플랜 요약)
- generation_prompt (사용한 프롬프트)
- created_at
- updated_at

daily_meal_plans
- id
- diet_plan_id
- day_number (1-7)
- date
- total_calories
- total_protein_g
- total_carbs_g
- total_fat_g
- tips (nullable)
- created_at
- updated_at

meal_plan_items
- id
- daily_meal_plan_id
- meal_type (breakfast, lunch, dinner, snack)
- food_id
- food_name
- serving_size
- calories
- protein_g
- carbs_g
- fat_g
- order
- notes (nullable)

daily_exercise_plans
- id
- diet_plan_id
- day_number (1-7)
- date
- exercise_id
- exercise_name
- duration_minutes
- estimated_calories_burned
- intensity
- notes (nullable)
- created_at
- updated_at
```

#### Queue 작업
```php
// Jobs/GenerateDietPlanJob.php
- 설문 데이터 수집
- 칼로리 계산
- GPT API 호출
- 응답 파싱 및 저장
- 사용자 알림 발송
```

#### API Endpoints
```
POST   /api/diet-plans/generate (비동기)
GET    /api/diet-plans/generation-status/{id}
GET    /api/diet-plans/active
GET    /api/diet-plans/{id}
GET    /api/diet-plans/{id}/day/{day}
```

#### 기술 스택
- OpenAI GPT-4 API
- Laravel Queue (Horizon)
- JSON 파싱 및 검증

#### 완료 조건
- [ ] GPT API 연동
- [ ] 프롬프트 최적화
- [ ] 응답 파싱 및 저장
- [ ] 비동기 처리 (Queue)
- [ ] 생성 실패 시 재시도 로직
- [ ] 생성 완료 알림

---

### Sub-Epic 1.3.4: 플랜 조회 & 수정

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 생성된 플랜을 요일별로 볼 수 있다
- [ ] 사용자로서, 특정 식사를 다른 음식으로 교체할 수 있다
- [ ] 사용자로서, 운동을 다른 운동으로 교체할 수 있다
- [ ] 사용자로서, 플랜이 마음에 안 들면 재생성할 수 있다

#### API Endpoints
```
GET    /api/diet-plans/{id}/meals
PUT    /api/diet-plans/meals/{mealItemId}/replace
GET    /api/diet-plans/{id}/exercises
PUT    /api/diet-plans/exercises/{exerciseId}/replace
POST   /api/diet-plans/{id}/regenerate
```

#### 교체 로직
- 같은 카테고리의 음식 중 비슷한 칼로리
- 같은 카테고리의 운동 중 비슷한 강도

#### 완료 조건
- [ ] 플랜 상세 조회 API
- [ ] 식사 교체 API (칼로리 자동 재계산)
- [ ] 운동 교체 API
- [ ] 플랜 재생성 (Queue)

---

## Epic 1.4: 일일 활동 기록 (기본)

**Phase**: 1 (MVP)
**Sprint**: 4 (Week 7-8)
**우선순위**: CRITICAL

### 목적
사용자가 매일 실천한 내용을 쉽게 기록하고 플랜 대비 달성률 확인

---

### Sub-Epic 1.4.1: 식사 기록

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 오늘 먹은 식사를 기록할 수 있다
- [ ] 사용자로서, 플랜에서 추천한 음식을 선택하여 기록할 수 있다
- [ ] 사용자로서, 플랜에 없는 음식을 검색하여 기록할 수 있다
- [ ] 사용자로서, 칼로리를 자동 계산받을 수 있다
- [ ] 사용자로서, 식사 시간을 기록할 수 있다

#### API Endpoints
```
POST   /api/daily-logs/meals
GET    /api/daily-logs/meals?date=2025-12-03
PUT    /api/daily-logs/meals/{id}
DELETE /api/daily-logs/meals/{id}
GET    /api/daily-logs/meals/summary?date=2025-12-03
```

#### 데이터 모델
```
meal_logs
- id
- user_id
- date
- meal_type (breakfast, lunch, dinner, snack)
- food_id (nullable)
- food_name
- serving_size
- calories
- protein_g
- carbs_g
- fat_g
- meal_time (nullable)
- notes (nullable)
- created_at
- updated_at
```

#### 완료 조건
- [ ] 식사 기록 CRUD API
- [ ] 플랜 식단 빠른 선택 기능
- [ ] 일일 칼로리 합계 계산
- [ ] 목표 대비 섭취 칼로리 비율 표시

---

### Sub-Epic 1.4.2: 운동 기록

**Story Points**: 3

#### User Stories
- [ ] 사용자로서, 오늘 한 운동을 기록할 수 있다
- [ ] 사용자로서, 플랜에서 추천한 운동을 체크할 수 있다
- [ ] 사용자로서, 운동 시간과 강도를 기록할 수 있다
- [ ] 사용자로서, 소모 칼로리를 자동 계산받을 수 있다

#### API Endpoints
```
POST   /api/daily-logs/exercises
GET    /api/daily-logs/exercises?date=2025-12-03
PUT    /api/daily-logs/exercises/{id}
DELETE /api/daily-logs/exercises/{id}
```

#### 데이터 모델
```
exercise_logs
- id
- user_id
- date
- exercise_id (nullable)
- exercise_name
- duration_minutes
- intensity (low, medium, high)
- calories_burned
- notes (nullable)
- created_at
- updated_at
```

#### 칼로리 계산
```php
소모 칼로리 = MET × 체중(kg) × 시간(h)
```

#### 완료 조건
- [ ] 운동 기록 CRUD API
- [ ] 칼로리 소모 자동 계산
- [ ] 일일 운동 시간 합계

---

### Sub-Epic 1.4.3: 체중 기록

**Story Points**: 2

#### User Stories
- [ ] 사용자로서, 매일 아침 체중을 기록할 수 있다
- [ ] 사용자로서, 체중 변화를 그래프로 볼 수 있다
- [ ] 사용자로서, 시작 체중 대비 변화량을 확인할 수 있다

#### API Endpoints
```
POST   /api/daily-logs/weight
GET    /api/daily-logs/weight?from=2025-11-01&to=2025-12-03
PUT    /api/daily-logs/weight/{id}
DELETE /api/daily-logs/weight/{id}
```

#### 데이터 모델
```
weight_logs
- id
- user_id
- date
- weight_kg
- body_fat_percentage (nullable)
- notes (nullable)
- created_at
- updated_at
```

#### 완료 조건
- [ ] 체중 기록 CRUD API
- [ ] 날짜별 체중 조회
- [ ] 변화량 계산

---

### Sub-Epic 1.4.4: 일일 대시보드

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 오늘의 플랜을 한눈에 볼 수 있다
- [ ] 사용자로서, 오늘 기록한 내용을 요약해서 볼 수 있다
- [ ] 사용자로서, 목표 칼로리 대비 섭취/소모 칼로리를 볼 수 있다
- [ ] 사용자로서, 오늘의 달성률을 백분율로 볼 수 있다

#### API Endpoints
```
GET    /api/daily-logs/dashboard?date=2025-12-03
GET    /api/daily-logs/summary?date=2025-12-03
```

#### 응답 형식
```json
{
  "date": "2025-12-03",
  "plan": {
    "meals": [...],
    "exercises": [...]
  },
  "logged": {
    "meals": [...],
    "exercises": [...],
    "weight": 70.5
  },
  "summary": {
    "target_calories": 1500,
    "consumed_calories": 1450,
    "burned_calories": 300,
    "net_calories": 1150,
    "completion_rate": 85
  }
}
```

#### 완료 조건
- [ ] 대시보드 API 구현
- [ ] 플랜 vs 실제 비교
- [ ] 달성률 계산 로직
- [ ] 일일 요약 정보

---

# Phase 2: 핵심 기능 강화

> **목표**: 사용자 경험 개선 및 지속 사용 유도
> **기간**: 4주
> **핵심**: 진행 상황 시각화 + 알림 시스템

---

## Epic 2.1: 진행 상황 모니터링

**Phase**: 2
**Sprint**: 5 (Week 9-10)
**우선순위**: HIGH

### 목적
사용자의 다이어트 진행 상황을 시각화하고 동기부여

---

### Sub-Epic 2.1.1: 진행 대시보드

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 전체 진행 상황을 한눈에 볼 수 있다
- [ ] 사용자로서, 목표까지 남은 기간과 체중을 확인할 수 있다
- [ ] 사용자로서, 현재까지의 성과를 요약해서 볼 수 있다

#### API Endpoints
```
GET    /api/progress/overview
GET    /api/progress/goal-status
```

#### 완료 조건
- [ ] 전체 진행 상황 API
- [ ] 목표 대비 달성률 계산
- [ ] D-day 계산

---

### Sub-Epic 2.1.2: 체중 변화 그래프

**Story Points**: 3

#### User Stories
- [ ] 사용자로서, 체중 변화를 꺾은선 그래프로 볼 수 있다
- [ ] 사용자로서, 일간/주간/월간 단위로 볼 수 있다
- [ ] 사용자로서, 추세선을 볼 수 있다

#### API Endpoints
```
GET    /api/progress/weight-trend?period=week|month|all
GET    /api/progress/statistics
```

#### 완료 조건
- [ ] 기간별 체중 데이터 조회
- [ ] 평균 감량 속도 계산
- [ ] 추세선 데이터

---

### Sub-Epic 2.1.3: 성취 시스템

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 목표를 달성하면 배지를 받을 수 있다
- [ ] 사용자로서, 연속 기록 일수(Streak)를 확인할 수 있다
- [ ] 사용자로서, 받은 배지를 모아볼 수 있다

#### 배지 종류
- 첫 기록 완료
- 7일 연속 기록
- 30일 연속 기록
- 첫 1kg 감량
- 목표 체중 달성
- 총 100회 운동
- 완벽한 한 주 (7일 모두 플랜 달성)

#### 데이터 모델
```
achievements
- id
- name
- description
- icon
- condition (json)
- created_at
- updated_at

user_achievements
- id
- user_id
- achievement_id
- achieved_at
- created_at
```

#### API Endpoints
```
GET    /api/achievements
GET    /api/achievements/my
POST   /api/achievements/check
```

#### 완료 조건
- [ ] 배지 정의 및 등록
- [ ] 달성 조건 체크 로직
- [ ] 배지 획득 알림

---

### Sub-Epic 2.1.4: 주간/월간 리포트

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 매주 일요일에 주간 리포트를 받을 수 있다
- [ ] 사용자로서, 지난주의 기록을 요약해서 볼 수 있다
- [ ] 사용자로서, 잘한 점과 개선할 점을 확인할 수 있다

#### 리포트 내용
- 주간 체중 변화
- 평균 칼로리 섭취
- 총 운동 시간
- 플랜 달성률
- 연속 기록 일수
- 다음 주 권장사항

#### 데이터 모델
```
weekly_reports
- id
- user_id
- week_start_date
- week_end_date
- weight_change_kg
- avg_calories_consumed
- total_exercise_minutes
- plan_completion_rate
- streak_days
- feedback (json)
- created_at
```

#### API Endpoints
```
GET    /api/reports/weekly
GET    /api/reports/weekly/{id}
POST   /api/reports/generate
```

#### Queue 작업
```php
// Jobs/GenerateWeeklyReportJob.php
- 매주 일요일 자정 실행
- 지난주 데이터 집계
- 리포트 생성 및 저장
- 이메일/알림 발송
```

#### 완료 조건
- [ ] 주간 데이터 집계
- [ ] 리포트 생성 로직
- [ ] 스케줄러 등록
- [ ] 이메일 발송

---

## Epic 2.2: 알림 & 리마인더

**Phase**: 2
**Sprint**: 6 (Week 11-12)
**우선순위**: HIGH

### 목적
사용자가 꾸준히 기록할 수 있도록 적절한 알림 제공

---

### Sub-Epic 2.2.1: 알림 설정

**Story Points**: 3

#### User Stories
- [ ] 사용자로서, 알림 시간을 내 일정에 맞게 설정할 수 있다
- [ ] 사용자로서, 원하는 알림만 켜고 끌 수 있다
- [ ] 사용자로서, 알림 방식(푸시/이메일)을 선택할 수 있다

#### 데이터 모델
```
user_notification_settings
- id
- user_id
- meal_reminder_enabled
- meal_reminder_times (json) [{"type": "breakfast", "time": "08:00"}, ...]
- exercise_reminder_enabled
- exercise_reminder_time
- weight_reminder_enabled
- weight_reminder_time
- daily_summary_enabled
- daily_summary_time
- weekly_report_enabled
- push_enabled
- email_enabled
- created_at
- updated_at
```

#### API Endpoints
```
GET    /api/notifications/settings
PUT    /api/notifications/settings
```

#### 완료 조건
- [ ] 알림 설정 CRUD
- [ ] 기본값 설정
- [ ] 설정 유효성 검증

---

### Sub-Epic 2.2.2: 스케줄 알림

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 식사 시간에 기록 알림을 받을 수 있다
- [ ] 사용자로서, 운동 시간에 운동 알림을 받을 수 있다
- [ ] 사용자로서, 아침에 체중 기록 알림을 받을 수 있다
- [ ] 사용자로서, 저녁에 오늘의 요약 알림을 받을 수 있다

#### 알림 종류
- 식사 기록 (아침 8시, 점심 12시, 저녁 7시)
- 운동 시간 (사용자 설정)
- 체중 기록 (아침 7시)
- 일일 요약 (저녁 9시)
- 격려 메시지 (랜덤)

#### Queue & Scheduler
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // 매시간 알림 체크
    $schedule->job(new CheckScheduledNotificationsJob)
             ->everyMinute();

    // 주간 리포트
    $schedule->job(new GenerateWeeklyReportsJob)
             ->weekly()->sundays()->at('00:00');
}
```

#### 데이터 모델
```
notifications
- id
- user_id
- type (meal_reminder, exercise_reminder, etc.)
- title
- message
- scheduled_at
- sent_at (nullable)
- read_at (nullable)
- data (json)
- created_at
- updated_at
```

#### API Endpoints
```
GET    /api/notifications
PUT    /api/notifications/{id}/read
DELETE /api/notifications/{id}
POST   /api/notifications/read-all
```

#### 완료 조건
- [ ] 스케줄러 설정
- [ ] 알림 발송 Job
- [ ] 사용자별 설정 반영
- [ ] 알림 이력 저장

---

### Sub-Epic 2.2.3: 푸시 알림 (선택)

**Story Points**: 8

#### User Stories
- [ ] 사용자로서, 앱을 열지 않아도 알림을 받을 수 있다
- [ ] 사용자로서, 푸시 알림을 허용하거나 거부할 수 있다

#### 기술 스택
- Firebase Cloud Messaging (FCM)
- Laravel Notifications

#### 데이터 모델
```
user_devices
- id
- user_id
- device_token (FCM token)
- platform (ios, android, web)
- is_active
- created_at
- updated_at
```

#### API Endpoints
```
POST   /api/devices/register
DELETE /api/devices/{id}
```

#### 완료 조건
- [ ] FCM 프로젝트 설정
- [ ] 디바이스 토큰 등록
- [ ] 푸시 알림 발송
- [ ] 배지 카운트

---

### Sub-Epic 2.2.4: 격려 메시지

**Story Points**: 3

#### User Stories
- [ ] 사용자로서, 목표 달성 시 축하 메시지를 받을 수 있다
- [ ] 사용자로서, 힘들 때 격려 메시지를 받을 수 있다
- [ ] 사용자로서, 연속 기록 시 응원 메시지를 받을 수 있다

#### 메시지 트리거
- 첫 기록 완료
- 7일 연속 기록
- 목표 체중 50% 달성
- 플래토 (정체기) 감지
- 일주일 기록 없음

#### 메시지 예시
```
"첫 기록 완료! 시작이 반입니다 💪"
"7일 연속 기록! 정말 잘하고 있어요 🔥"
"목표의 절반을 달성했어요! 조금만 더 파이팅! 🎉"
"3일간 체중 변화가 없네요. 괜찮아요, 꾸준히 하다보면 변화가 올 거예요 😊"
```

#### 완료 조건
- [ ] 메시지 템플릿 작성
- [ ] 트리거 조건 구현
- [ ] 자동 발송 로직

---

## Epic 2.3: 일일 활동 기록 (고급)

**Phase**: 2
**Sprint**: 병렬 진행
**우선순위**: MEDIUM

### Sub-Epic 2.3.1: 식사 사진 업로드

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 먹은 음식 사진을 찍어 올릴 수 있다
- [ ] 사용자로서, 업로드한 사진을 기록과 함께 볼 수 있다

#### 기술 스택
- Laravel Storage
- Image Intervention (리사이징)

#### 데이터 모델
```
meal_logs
+ photo_url (nullable)
```

#### API Endpoints
```
POST   /api/daily-logs/meals/photo
DELETE /api/daily-logs/meals/photo/{id}
```

#### 완료 조건
- [ ] 이미지 업로드 API
- [ ] 리사이징 (최대 1MB)
- [ ] 썸네일 생성

---

### Sub-Epic 2.3.2: 물 섭취 & 수면 기록

**Story Points**: 3

#### User Stories
- [ ] 사용자로서, 마신 물의 양을 기록할 수 있다
- [ ] 사용자로서, 수면 시간을 기록할 수 있다

#### 데이터 모델
```
water_logs
- id
- user_id
- date
- amount_ml
- logged_at
- created_at

sleep_logs
- id
- user_id
- date
- sleep_time
- wake_time
- duration_hours
- quality (1-5)
- notes (nullable)
- created_at
```

#### API Endpoints
```
POST   /api/daily-logs/water
GET    /api/daily-logs/water?date=

POST   /api/daily-logs/sleep
GET    /api/daily-logs/sleep?date=
```

#### 완료 조건
- [ ] 물 섭취 기록 API
- [ ] 수면 기록 API
- [ ] 일일 목표 (물 2L, 수면 7시간)

---

### Sub-Epic 2.3.3: 메모 & 일기

**Story Points**: 2

#### User Stories
- [ ] 사용자로서, 오늘의 컨디션을 메모할 수 있다
- [ ] 사용자로서, 느낀 점을 일기로 남길 수 있다

#### 데이터 모델
```
daily_logs
+ notes (text, nullable)
+ mood (1-5, nullable)
```

#### API Endpoints
```
PUT    /api/daily-logs/{date}/notes
```

#### 완료 조건
- [ ] 메모 저장 API
- [ ] 기분 선택 (이모지)

---

# Phase 3: 성장 기능

> **목표**: 서비스 확장 및 운영 효율화
> **기간**: 4주
> **핵심**: 관리자 도구 + 커뮤니티

---

## Epic 3.1: 관리자 기능

**Phase**: 3
**Sprint**: 7 (Week 13-14)
**우선순위**: MEDIUM

### Sub-Epic 3.1.1: 사용자 관리

**Story Points**: 5

#### User Stories
- [ ] 관리자로서, 전체 사용자 목록을 볼 수 있다
- [ ] 관리자로서, 사용자를 검색할 수 있다
- [ ] 관리자로서, 사용자를 비활성화할 수 있다

#### API Endpoints
```
GET    /api/admin/users
GET    /api/admin/users/{id}
PUT    /api/admin/users/{id}/suspend
```

#### 완료 조건
- [ ] 사용자 목록 API
- [ ] 검색 및 필터
- [ ] 사용자 상세 정보

---

### Sub-Epic 3.1.2: 데이터 관리

**Story Points**: 5

#### User Stories
- [ ] 관리자로서, 음식 데이터를 추가/수정할 수 있다
- [ ] 관리자로서, 운동 데이터를 추가/수정할 수 있다

#### API Endpoints
```
GET    /api/admin/foods
POST   /api/admin/foods
PUT    /api/admin/foods/{id}
DELETE /api/admin/foods/{id}

GET    /api/admin/exercises
POST   /api/admin/exercises
PUT    /api/admin/exercises/{id}
DELETE /api/admin/exercises/{id}
```

#### 완료 조건
- [ ] CRUD API 구현
- [ ] 벌크 업로드 (CSV)

---

### Sub-Epic 3.1.3: 통계 대시보드

**Story Points**: 5

#### User Stories
- [ ] 관리자로서, 전체 통계를 한눈에 볼 수 있다
- [ ] 관리자로서, 일간/주간/월간 가입자 수를 확인할 수 있다
- [ ] 관리자로서, 활성 사용자 비율을 확인할 수 있다

#### 통계 항목
- 총 가입자 수
- 활성 사용자 (최근 7일 로그인)
- 플랜 생성 수
- 일일 기록 수
- 목표 달성자 수

#### API Endpoints
```
GET    /api/admin/statistics
GET    /api/admin/statistics/users
GET    /api/admin/statistics/activities
```

#### 완료 조건
- [ ] 통계 집계 로직
- [ ] 대시보드 API
- [ ] 차트 데이터

---

## Epic 3.2: 커뮤니티

**Phase**: 3
**Sprint**: 8 (Week 15-16)
**우선순위**: LOW

### Sub-Epic 3.2.1: 게시판

**Story Points**: 8

#### User Stories
- [ ] 사용자로서, 내 경험을 글로 공유할 수 있다
- [ ] 사용자로서, 다른 사람의 글을 보고 응원할 수 있다
- [ ] 사용자로서, 댓글을 달 수 있다

#### 데이터 모델
```
posts
- id
- user_id
- category (success_story, tip, question)
- title
- content
- images (json)
- likes_count
- comments_count
- created_at
- updated_at

comments
- id
- post_id
- user_id
- parent_id (nullable, 대댓글)
- content
- likes_count
- created_at
- updated_at

likes
- id
- user_id
- likeable_type (post, comment)
- likeable_id
- created_at
```

#### API Endpoints
```
GET    /api/posts
POST   /api/posts
GET    /api/posts/{id}
PUT    /api/posts/{id}
DELETE /api/posts/{id}

POST   /api/posts/{id}/comments
GET    /api/posts/{id}/comments
DELETE /api/comments/{id}

POST   /api/posts/{id}/like
DELETE /api/posts/{id}/unlike
```

#### 완료 조건
- [ ] 게시판 CRUD
- [ ] 댓글 기능
- [ ] 좋아요 기능
- [ ] 이미지 업로드

---

### Sub-Epic 3.2.2: 챌린지

**Story Points**: 8

#### User Stories
- [ ] 사용자로서, 챌린지에 참여할 수 있다
- [ ] 사용자로서, 챌린지 진행 상황을 공유할 수 있다
- [ ] 사용자로서, 완료 시 배지를 받을 수 있다

#### 챌린지 예시
- 30일 연속 기록
- 한 달 5kg 감량
- 100회 운동 완료

#### 데이터 모델
```
challenges
- id
- name
- description
- goal_type (weight_loss, exercise_count, streak)
- goal_value
- start_date
- end_date
- participants_count
- created_at

challenge_participants
- id
- challenge_id
- user_id
- status (active, completed, failed)
- progress
- joined_at
- completed_at (nullable)
```

#### API Endpoints
```
GET    /api/challenges
GET    /api/challenges/{id}
POST   /api/challenges/{id}/join
GET    /api/challenges/my
```

#### 완료 조건
- [ ] 챌린지 조회/참여
- [ ] 진행률 자동 계산
- [ ] 완료 시 배지 지급

---

### Sub-Epic 3.2.3: 친구 기능

**Story Points**: 5

#### User Stories
- [ ] 사용자로서, 다른 사용자를 친구로 추가할 수 있다
- [ ] 사용자로서, 친구의 진행 상황을 볼 수 있다
- [ ] 사용자로서, 친구에게 응원 메시지를 보낼 수 있다

#### 데이터 모델
```
friendships
- id
- user_id
- friend_id
- status (pending, accepted, rejected)
- created_at
- updated_at
```

#### API Endpoints
```
POST   /api/friends/request
PUT    /api/friends/{id}/accept
DELETE /api/friends/{id}
GET    /api/friends
GET    /api/friends/{id}/progress
```

#### 완료 조건
- [ ] 친구 요청/수락
- [ ] 친구 목록
- [ ] 친구 진행 상황 (공개 설정된 경우)

---

# 전체 요약

## Phase별 Epic 구조

### Phase 1: MVP (4-6주)
```
Epic 1.1: 사용자 인증 시스템
├── Sub-Epic 1.1.1: 이메일 회원가입/로그인 (5pt)
├── Sub-Epic 1.1.2: 프로필 관리 (3pt)
├── Sub-Epic 1.1.3: 소셜 로그인 (5pt)
└── Sub-Epic 1.1.4: 비밀번호 찾기 (3pt)

Epic 1.2: 온보딩 & 설문
├── Sub-Epic 1.2.1: 설문 인프라 (5pt)
├── Sub-Epic 1.2.2: 기본 정보 설문 (3pt)
├── Sub-Epic 1.2.3: 목표 설정 설문 (2pt)
├── Sub-Epic 1.2.4: 생활 패턴 설문 (3pt)
├── Sub-Epic 1.2.5: 건강 & 선호도 설문 (3pt)
└── Sub-Epic 1.2.6: 설문 관리 기능 (2pt)

Epic 1.3: AI 다이어트 플랜 생성
├── Sub-Epic 1.3.1: 칼로리 계산 엔진 (5pt)
├── Sub-Epic 1.3.2: 음식 & 운동 DB (8pt)
├── Sub-Epic 1.3.3: AI 플랜 생성 (13pt)
└── Sub-Epic 1.3.4: 플랜 조회 & 수정 (5pt)

Epic 1.4: 일일 활동 기록 (기본)
├── Sub-Epic 1.4.1: 식사 기록 (5pt)
├── Sub-Epic 1.4.2: 운동 기록 (3pt)
├── Sub-Epic 1.4.3: 체중 기록 (2pt)
└── Sub-Epic 1.4.4: 일일 대시보드 (5pt)

Total: 80 Story Points
```

### Phase 2: 핵심 기능 (4주)
```
Epic 2.1: 진행 상황 모니터링
├── Sub-Epic 2.1.1: 진행 대시보드 (5pt)
├── Sub-Epic 2.1.2: 체중 변화 그래프 (3pt)
├── Sub-Epic 2.1.3: 성취 시스템 (5pt)
└── Sub-Epic 2.1.4: 주간/월간 리포트 (5pt)

Epic 2.2: 알림 & 리마인더
├── Sub-Epic 2.2.1: 알림 설정 (3pt)
├── Sub-Epic 2.2.2: 스케줄 알림 (5pt)
├── Sub-Epic 2.2.3: 푸시 알림 (8pt)
└── Sub-Epic 2.2.4: 격려 메시지 (3pt)

Epic 2.3: 일일 활동 기록 (고급)
├── Sub-Epic 2.3.1: 식사 사진 업로드 (5pt)
├── Sub-Epic 2.3.2: 물 섭취 & 수면 기록 (3pt)
└── Sub-Epic 2.3.3: 메모 & 일기 (2pt)

Total: 47 Story Points
```

### Phase 3: 성장 기능 (4주)
```
Epic 3.1: 관리자 기능
├── Sub-Epic 3.1.1: 사용자 관리 (5pt)
├── Sub-Epic 3.1.2: 데이터 관리 (5pt)
└── Sub-Epic 3.1.3: 통계 대시보드 (5pt)

Epic 3.2: 커뮤니티
├── Sub-Epic 3.2.1: 게시판 (8pt)
├── Sub-Epic 3.2.2: 챌린지 (8pt)
└── Sub-Epic 3.2.3: 친구 기능 (5pt)

Total: 36 Story Points
```

## 총 Story Points: 163

---

# 다음 단계

1. [ ] Sprint 1 시작 - Epic 1.1 (사용자 인증)
2. [ ] 데이터베이스 ERD 설계
3. [ ] API 명세서 작성 (Swagger)
4. [ ] 설문 문항 최종 확정
