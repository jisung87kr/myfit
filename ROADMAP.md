# MyFit 프로젝트 로드맵

## 프로젝트 비전

**"다이어트가 처음인 사람도 AI의 도움으로 쉽게 시작하고 꾸준히 실천할 수 있는 맞춤형 다이어트 플랜 서비스"**

---

## 📅 개발 로드맵

### ✅ Phase 0: 프로젝트 초기 설정 (완료)
**기간**: Week 1

**완료 항목**:
- [x] Laravel 12 프로젝트 초기화
- [x] Docker 환경 구성 (PHP 8.4, MySQL, Redis)
- [x] Laravel Sanctum 설치 (API 인증)
- [x] Laravel Horizon 설치 (큐 관리)
- [x] Spatie Permission 설치 (권한 관리)
- [x] 프로젝트 문서화 (DOCKER.md, SETUP.md)
- [x] 에픽 정의 (EPICS.md)

---

### 🎯 Phase 1: MVP 개발 (4-6주)

#### Sprint 1: 사용자 인증 시스템 (Week 2-3)
**Epic 1: 사용자 관리**

- [ ] 데이터베이스 스키마 설계
  - users 테이블 확장
  - profiles 테이블

- [ ] API 엔드포인트 개발
  - POST /api/register
  - POST /api/login
  - POST /api/logout
  - GET /api/user
  - PUT /api/user/profile
  - POST /api/password/reset

- [ ] 소셜 로그인
  - Google OAuth
  - Kakao OAuth

- [ ] 테스트 작성
  - Feature tests
  - Unit tests

**결과물**: 사용자가 회원가입하고 로그인할 수 있는 API

---

#### Sprint 2: 온보딩 & 설문 시스템 (Week 3-4)
**Epic 2: 온보딩 & 설문**

- [ ] 데이터베이스 스키마 설계
  - surveys 테이블
  - survey_questions 테이블
  - user_survey_responses 테이블
  - user_goals 테이블

- [ ] 설문 데이터 설계
  - 기본 정보 설문 (5문항)
  - 목표 설정 설문 (3문항)
  - 생활 패턴 설문 (5문항)
  - 건강 상태 설문 (3문항)
  - 식품 선호도 설문 (5문항)

- [ ] API 엔드포인트 개발
  - GET /api/surveys (설문지 가져오기)
  - POST /api/surveys/responses (응답 저장)
  - PUT /api/surveys/responses/{id} (응답 수정)
  - GET /api/surveys/progress (진행률 확인)

- [ ] 온보딩 플로우 구현
  - 다단계 설문 로직
  - 임시 저장 기능
  - 유효성 검증

**결과물**: 사용자가 설문을 완료하고 목표를 설정할 수 있는 시스템

---

#### Sprint 3: AI 다이어트 플랜 생성 (Week 5-6)
**Epic 3: AI 다이어트 플랜 생성**

- [ ] 데이터베이스 스키마 설계
  - diet_plans 테이블
  - meal_plans 테이블
  - meals 테이블
  - foods 테이블
  - exercise_plans 테이블
  - exercises 테이블

- [ ] 칼로리 계산 엔진
  - BMR (기초대사량) 계산
  - TDEE (일일 총 에너지 소비량) 계산
  - 목표 칼로리 계산

- [ ] 음식 데이터베이스 구축
  - 한국 음식 100가지 (기본)
  - 칼로리, 영양소 정보

- [ ] 운동 데이터베이스 구축
  - 기본 운동 50가지
  - 강도별 칼로리 소모량

- [ ] OpenAI GPT API 연동
  - 프롬프트 엔지니어링
  - 맞춤형 식단 생성
  - 맞춤형 운동 생성

- [ ] API 엔드포인트 개발
  - POST /api/diet-plans/generate (플랜 생성)
  - GET /api/diet-plans (내 플랜 조회)
  - POST /api/diet-plans/regenerate (재생성)
  - PUT /api/diet-plans/meals/{id}/replace (식사 교체)

- [ ] Queue 작업 구현
  - AI 플랜 생성 (비동기)
  - 알림 발송

**결과물**: AI가 생성한 맞춤형 다이어트 플랜

---

#### Sprint 4: 일일 활동 기록 (Week 7-8)
**Epic 4: 일일 활동 기록 (기본 기능)**

- [ ] 데이터베이스 스키마 설계
  - daily_logs 테이블
  - meal_logs 테이블
  - exercise_logs 테이블
  - weight_logs 테이블
  - water_logs 테이블

- [ ] API 엔드포인트 개발
  - POST /api/daily-logs/meals (식사 기록)
  - POST /api/daily-logs/exercises (운동 기록)
  - POST /api/daily-logs/weight (체중 기록)
  - POST /api/daily-logs/water (물 섭취 기록)
  - GET /api/daily-logs/{date} (특정 날짜 기록 조회)
  - GET /api/daily-logs/summary (오늘의 요약)

- [ ] 이미지 업로드
  - 식사 사진 업로드
  - S3 또는 로컬 스토리지

- [ ] 달성률 계산 로직
  - 플랜 대비 실제 실행률

**결과물**: 사용자가 매일 식사, 운동, 체중을 기록할 수 있는 시스템

---

### 🚀 Phase 2: 핵심 기능 강화 (4주)

#### Sprint 5: 진행 상황 모니터링 (Week 9-10)
**Epic 5: 진행 상황 모니터링**

- [ ] 통계 계산 로직
  - 일간, 주간, 월간 통계
  - 체중 변화 추이
  - 칼로리 섭취/소모 분석

- [ ] API 엔드포인트 개발
  - GET /api/progress/dashboard (대시보드)
  - GET /api/progress/weight-trend (체중 추이)
  - GET /api/progress/statistics (통계)
  - GET /api/progress/achievements (성취 목록)

- [ ] 성취 시스템
  - 배지 정의
  - 배지 획득 로직

- [ ] 리포트 생성
  - 주간 리포트 (PDF/이메일)

**결과물**: 진행 상황을 시각화하고 동기부여하는 대시보드

---

#### Sprint 6: 알림 시스템 (Week 11-12)
**Epic 6: 알림 & 리마인더**

- [ ] 데이터베이스 스키마 설계
  - user_notification_settings 테이블
  - notifications 테이블

- [ ] 알림 로직 구현
  - 식사 시간 알림
  - 운동 알림
  - 체중 기록 알림
  - 격려 메시지

- [ ] Queue 작업
  - 스케줄러를 통한 정시 알림
  - Horizon 모니터링

- [ ] 푸시 알림 (선택)
  - FCM 연동

- [ ] API 엔드포인트 개발
  - GET /api/notifications/settings (알림 설정 조회)
  - PUT /api/notifications/settings (알림 설정 수정)

**결과물**: 사용자에게 적절한 타이밍에 알림을 보내는 시스템

---

### 📈 Phase 3: 성장 기능 (4주)

#### Sprint 7: 관리자 대시보드 (Week 13-14)
**Epic 8: 관리자 기능**

- [ ] 관리자 페이지 구축
  - 사용자 관리
  - 통계 대시보드
  - 음식/운동 데이터 관리

- [ ] API 엔드포인트 개발
  - GET /api/admin/users (사용자 목록)
  - GET /api/admin/statistics (통계)
  - POST /api/admin/foods (음식 추가)
  - POST /api/admin/exercises (운동 추가)

**결과물**: 서비스를 효율적으로 관리할 수 있는 관리자 도구

---

#### Sprint 8: 커뮤니티 기능 (Week 15-16)
**Epic 7: 커뮤니티 (선택)**

- [ ] 데이터베이스 스키마 설계
  - posts 테이블
  - comments 테이블
  - likes 테이블
  - challenges 테이블

- [ ] API 엔드포인트 개발
  - 게시글 CRUD
  - 댓글 CRUD
  - 좋아요 기능
  - 챌린지 기능

**결과물**: 사용자 간 소통할 수 있는 커뮤니티

---

## 🎨 프론트엔드 개발 (병렬 진행 가능)

### 기술 스택 결정
- [ ] Vue.js 3 vs React 선택
- [ ] UI 라이브러리 선택 (Tailwind CSS, Vuetify 등)
- [ ] 상태 관리 (Pinia/Vuex, Redux)

### 화면 설계
- [ ] 와이어프레임 작성
- [ ] UI/UX 디자인
- [ ] 프로토타입 제작

### 개발
- [ ] 인증 화면
- [ ] 온보딩 화면
- [ ] 대시보드
- [ ] 기록 화면
- [ ] 통계 화면

---

## 📊 성공 지표 (KPI)

### MVP 출시 기준
- [ ] 회원가입/로그인 완성도 99%
- [ ] 설문 완료율 80% 이상
- [ ] AI 플랜 생성 성공률 95% 이상
- [ ] 일일 기록 기능 정상 작동
- [ ] 응답 시간 < 2초

### 베타 테스트 목표
- 베타 사용자 50명
- 7일 리텐션 40% 이상
- 평균 일일 기록 횟수 2회 이상

### 정식 출시 목표
- 가입자 1,000명
- 30일 리텐션 25% 이상
- 목표 달성률 15% 이상

---

## 🛠️ 기술 부채 관리

### 코드 품질
- [ ] ESLint/Prettier 설정
- [ ] PHP CS Fixer 설정
- [ ] 테스트 커버리지 70% 이상
- [ ] 코드 리뷰 프로세스

### 보안
- [ ] API Rate Limiting
- [ ] CORS 설정
- [ ] XSS 방지
- [ ] SQL Injection 방지
- [ ] 민감 정보 암호화

### 성능
- [ ] 데이터베이스 쿼리 최적화
- [ ] Redis 캐싱 전략
- [ ] 이미지 최적화
- [ ] CDN 적용 (선택)

---

## 📝 다음 액션 아이템

### 즉시 시작 가능
1. [x] ~~에픽 정의 완료~~
2. [ ] Epic 2 (온보딩 & 설문) 상세 User Story 작성
3. [ ] 데이터베이스 ERD 설계
4. [ ] API 명세서 작성 (Swagger/OpenAPI)
5. [ ] 설문 문항 작성 (총 21문항)

### 이번 주 목표
- [ ] Sprint 1 시작 (사용자 인증 시스템)
- [ ] 데이터베이스 마이그레이션 파일 작성
- [ ] 회원가입/로그인 API 구현

---

## 📞 연락 및 협업

- 프로젝트 관리: GitHub Projects
- 코드 리뷰: Pull Request
- 문서화: Markdown (GitHub)
- API 문서: Postman/Swagger
