# MyFit 프론트엔드 PRD (Product Requirements Document)

## 문서 정보
- **작성일**: 2025-12-05
- **버전**: 1.0
- **작성자**: MyFit Development Team
- **프로젝트**: MyFit AI 다이어트 플래너 웹 애플리케이션

---

## 1. 프로젝트 개요

### 1.1 목적
MyFit은 AI 기반 맞춤 다이어트 플랜을 제공하고, 사용자가 일일 식사, 운동, 체중을 기록하여 건강 목표를 달성할 수 있도록 돕는 웹 애플리케이션입니다.

### 1.2 범위
- **대상 사용자**: 다이어트 및 건강 관리에 관심 있는 성인 (20-50대)
- **플랫폼**: 웹 (반응형 - 모바일, 태블릿, 데스크톱)
- **기술 스택**: Laravel Blade + Vue.js 3 (CDN) + Tailwind CSS

### 1.3 현재 상태
- ✅ 백엔드 API 완전 구현 (80pt)
- ✅ 기본 프론트엔드 구조 (레이아웃, 인증, 대시보드)
- ⏳ 핵심 기능 페이지 구현 필요

---

## 2. 비즈니스 목표

### 2.1 주요 목표
1. 사용자가 쉽게 일일 활동을 기록할 수 있는 직관적인 UI 제공
2. AI 다이어트 플랜을 시각적으로 명확하게 표시
3. 진행 상황을 한눈에 파악할 수 있는 대시보드
4. 모바일 우선 반응형 디자인

### 2.2 성공 지표
- 페이지 로딩 시간 < 2초
- 모바일 반응성 100%
- 사용자 작업 완료율 > 90%
- 직관적 UI (사용자 테스트 만족도 > 4.5/5)

---

## 3. 사용자 스토리

### 3.1 신규 사용자
```
AS A 신규 사용자
I WANT TO 간단하게 회원가입하고 설문을 완료하여
SO THAT 나에게 맞는 AI 다이어트 플랜을 받을 수 있다
```

### 3.2 일반 사용자
```
AS A 일반 사용자
I WANT TO 식사, 운동, 체중을 빠르게 기록하고
SO THAT 내 진행 상황을 추적하고 목표를 달성할 수 있다
```

### 3.3 목표 달성 사용자
```
AS A 목표 달성 사용자
I WANT TO 내 성과를 시각적으로 확인하고
SO THAT 동기부여를 얻고 건강한 습관을 유지할 수 있다
```

---

## 4. 페이지 구조 및 우선순위

### Phase 1: 핵심 기능 (필수) - Week 1-2
| 순서 | 페이지 | 우선순위 | 상태 | 예상 시간 |
|------|--------|----------|------|-----------|
| 1 | 로그인/회원가입 | P0 | ✅ 완료 | - |
| 2 | 대시보드 | P0 | ✅ 완료 | - |
| 3 | 식사 기록 목록 | P0 | ⏳ 대기 | 4h |
| 4 | 식사 추가/수정 | P0 | ⏳ 대기 | 6h |
| 5 | 운동 기록 목록 | P0 | ⏳ 대기 | 4h |
| 6 | 운동 추가/수정 | P0 | ⏳ 대기 | 6h |
| 7 | 체중 기록 | P0 | ⏳ 대기 | 5h |

### Phase 2: 확장 기능 (중요) - Week 3
| 순서 | 페이지 | 우선순위 | 상태 | 예상 시간 |
|------|--------|----------|------|-----------|
| 8 | 다이어트 플랜 보기 | P1 | ⏳ 대기 | 6h |
| 9 | 플랜 식사/운동 대체 | P1 | ⏳ 대기 | 4h |
| 10 | 주간 요약 | P1 | ⏳ 대기 | 5h |

### Phase 3: 추가 기능 (선택) - Week 4
| 순서 | 페이지 | 우선순위 | 상태 | 예상 시간 |
|------|--------|----------|------|-----------|
| 11 | 프로필 관리 | P2 | ⏳ 대기 | 4h |
| 12 | 설문 페이지 | P2 | ⏳ 대기 | 6h |
| 13 | 칼로리 계산기 | P2 | ⏳ 대기 | 3h |

---

## 5. 상세 페이지 요구사항

### 5.1 식사 기록 페이지

#### 5.1.1 식사 목록 페이지 (`/meals`)
**목적**: 오늘의 식사 기록을 확인하고 관리

**주요 기능**:
- [ ] 날짜 선택기 (기본값: 오늘)
- [ ] 식사 타입별 그룹화 (아침/점심/저녁/간식)
- [ ] 각 식사 카드:
  - 식사 이름
  - 섭취량
  - 칼로리 및 영양소 (단백질/탄수화물/지방)
  - 시간
  - 수정/삭제 버튼
- [ ] 일일 영양 요약 (하단 고정)
  - 총 칼로리
  - 목표 대비 퍼센트
  - 영양소 비율
- [ ] 빠른 추가 버튼 (플로팅)

**UI 컴포넌트**:
```vue
<meal-list-page>
  <date-picker />
  <nutrition-summary-card />
  <meal-type-section type="breakfast">
    <meal-card />
    <meal-card />
  </meal-type-section>
  <meal-type-section type="lunch">
    ...
  </meal-type-section>
  <floating-add-button />
</meal-list-page>
```

**API 연동**:
- GET `/api/daily-logs/meals?date={date}`
- DELETE `/api/daily-logs/meals/{id}`

**성공 기준**:
- [ ] 모든 식사가 타입별로 명확히 표시
- [ ] 영양 정보가 시각적으로 이해하기 쉬움
- [ ] 삭제 시 확인 모달 표시
- [ ] 모바일에서 터치하기 쉬운 버튼 크기

---

#### 5.1.2 식사 추가/수정 페이지 (`/meals/create`, `/meals/{id}/edit`)
**목적**: 새로운 식사를 기록하거나 기존 기록을 수정

**주요 기능**:
- [ ] 입력 방식 선택:
  - 식품 DB에서 검색
  - 수동 입력
  - 플랜에서 추가
- [ ] 식품 DB 검색:
  - 실시간 검색 (자동완성)
  - 카테고리 필터
  - 최근 사용 식품
- [ ] 입력 필드:
  - 날짜 (기본: 오늘)
  - 식사 타입 (아침/점심/저녁/간식)
  - 식품 이름
  - 섭취량 (g)
  - 칼로리 (자동 계산 또는 수동)
  - 영양소 (자동 계산 또는 수동)
  - 식사 시간
  - 메모
- [ ] 영양소 미리보기
- [ ] 저장/취소 버튼

**UI 컴포넌트**:
```vue
<meal-form-page>
  <input-method-selector />

  <!-- 식품 DB 검색 모드 -->
  <food-search-input />
  <food-search-results />
  <recent-foods />

  <!-- 수동 입력 모드 -->
  <meal-form>
    <date-input />
    <meal-type-selector />
    <food-name-input />
    <serving-size-input />
    <nutrition-inputs />
    <time-input />
    <notes-textarea />
  </meal-form>

  <nutrition-preview-card />
  <form-actions />
</meal-form-page>
```

**API 연동**:
- GET `/api/foods?search={query}` - 식품 검색
- GET `/api/foods/{id}` - 식품 상세
- POST `/api/daily-logs/meals` - 식사 추가
- PUT `/api/daily-logs/meals/{id}` - 식사 수정
- POST `/api/daily-logs/meals/from-plan` - 플랜에서 추가

**성공 기준**:
- [ ] 식품 검색이 빠르고 정확함
- [ ] 영양소가 자동 계산됨
- [ ] 유효성 검사가 명확함
- [ ] 저장 후 목록으로 자동 이동
- [ ] 오류 메시지가 이해하기 쉬움

---

### 5.2 운동 기록 페이지

#### 5.2.1 운동 목록 페이지 (`/exercises`)
**목적**: 오늘의 운동 기록을 확인하고 관리

**주요 기능**:
- [ ] 날짜 선택기
- [ ] 운동 카드 목록:
  - 운동 이름
  - 시간 (분)
  - 소모 칼로리
  - 강도
  - 수정/삭제 버튼
- [ ] 일일 운동 요약:
  - 총 운동 시간
  - 총 소모 칼로리
  - 운동 횟수
- [ ] 빠른 추가 버튼

**UI 컴포넌트**:
```vue
<exercise-list-page>
  <date-picker />
  <exercise-summary-card />
  <exercise-card-list>
    <exercise-card />
  </exercise-card-list>
  <floating-add-button />
</exercise-list-page>
```

**API 연동**:
- GET `/api/daily-logs/exercises?date={date}`
- DELETE `/api/daily-logs/exercises/{id}`

---

#### 5.2.2 운동 추가/수정 페이지 (`/exercises/create`)
**목적**: 새로운 운동을 기록하거나 수정

**주요 기능**:
- [ ] 입력 방식 선택:
  - 운동 DB에서 검색
  - 수동 입력
  - 플랜에서 추가
- [ ] 운동 DB 검색:
  - 실시간 검색
  - 카테고리 필터 (유산소/근력/스포츠)
  - 강도별 필터
- [ ] 입력 필드:
  - 날짜
  - 운동 이름
  - 운동 시간 (분)
  - 소모 칼로리 (자동 계산 또는 수동)
  - 강도
  - 운동 시간
  - 메모
- [ ] 칼로리 미리보기

**API 연동**:
- GET `/api/exercises?search={query}`
- POST `/api/daily-logs/exercises`
- POST `/api/daily-logs/exercises/from-plan`

---

### 5.3 체중 기록 페이지

#### 5.3.1 체중 기록 메인 (`/weight`)
**목적**: 체중 변화 추적 및 진행 상황 확인

**주요 기능**:
- [ ] 현재 체중 대형 표시
- [ ] 체중 변화 차트 (라인 차트)
  - 기간 선택 (7일/30일/90일/전체)
  - 목표 체중 라인
- [ ] 체중 진행 상황:
  - 시작 체중
  - 현재 체중
  - 변화량 (kg, %)
  - 목표까지 남은 양
- [ ] 체중 기록 목록 (테이블)
  - 날짜
  - 체중
  - 변화량
  - 메모
  - 수정/삭제
- [ ] 체중 추가 버튼

**UI 컴포넌트**:
```vue
<weight-page>
  <current-weight-card />
  <weight-chart>
    <period-selector />
    <line-chart />
  </weight-chart>
  <progress-summary />
  <weight-log-table />
  <add-weight-button />
</weight-page>
```

**API 연동**:
- GET `/api/weight-logs/latest`
- GET `/api/weight-logs/history?days={days}`
- GET `/api/weight-logs/progress`
- POST `/api/weight-logs`
- DELETE `/api/weight-logs/{id}`

---

### 5.4 다이어트 플랜 페이지

#### 5.4.1 플랜 보기 (`/diet-plan`)
**목적**: AI 생성 7일 다이어트 플랜 확인

**주요 기능**:
- [ ] 플랜 상태 표시:
  - 생성 중
  - 활성
  - 완료
  - 보관
- [ ] 플랜 요약:
  - 생성일
  - 목표 칼로리
  - AI 요약
- [ ] 7일 탭:
  - Day 1 ~ Day 7
  - 각 날짜에 대한:
    - 식사 계획 (아침/점심/저녁/간식)
    - 운동 계획
    - 영양 요약
- [ ] 식사/운동 대체 기능:
  - 대체 제안 보기
  - 선택하여 대체
- [ ] 플랜 재생성 버튼
- [ ] 빠른 추가 버튼 (플랜 → 일일 로그)

**UI 컴포넌트**:
```vue
<diet-plan-page>
  <plan-status-badge />
  <plan-summary-card />
  <day-tabs>
    <day-tab day="1">
      <meal-plan-section>
        <meal-plan-item>
          <replace-button />
        </meal-plan-item>
      </meal-plan-section>
      <exercise-plan-section>
        <exercise-plan-item>
          <replace-button />
        </exercise-plan-item>
      </exercise-plan-section>
      <nutrition-summary />
    </day-tab>
  </day-tabs>
  <regenerate-plan-button />
</diet-plan-page>
```

**API 연동**:
- GET `/api/diet-plans/active`
- GET `/api/diet-plans/{id}/day/{day}`
- PUT `/api/diet-plans/meals/{id}/replace`
- PUT `/api/diet-plans/exercises/{id}/replace`
- POST `/api/diet-plans/{id}/regenerate`

---

## 6. UI/UX 디자인 가이드

### 6.1 색상 팔레트
```css
Primary (Green): #10b981
Secondary (Blue): #3b82f6
Success: #22c55e
Warning: #f59e0b
Error: #ef4444
Gray-50: #f9fafb
Gray-100: #f3f4f6
Gray-500: #6b7280
Gray-900: #111827
```

### 6.2 타이포그래피
```css
Headings: font-weight: 700 (bold)
Body: font-weight: 400 (normal)
Buttons: font-weight: 500 (medium)

H1: text-3xl (30px)
H2: text-2xl (24px)
H3: text-xl (20px)
Body: text-base (16px)
Small: text-sm (14px)
```

### 6.3 컴포넌트 스타일
- **카드**: `bg-white shadow rounded-lg p-6`
- **버튼 (Primary)**: `bg-primary hover:bg-primary/90 text-white rounded-md px-4 py-2`
- **입력 필드**: `border border-gray-300 rounded-md px-3 py-2 focus:ring-primary focus:border-primary`
- **배지**: `px-2 py-1 text-xs rounded-full`

### 6.4 반응형 브레이크포인트
```css
Mobile: < 640px
Tablet: 640px ~ 1024px
Desktop: > 1024px
```

---

## 7. 기술 요구사항

### 7.1 성능
- [ ] 페이지 로딩: < 2초
- [ ] API 응답 표시: < 500ms
- [ ] 차트 렌더링: < 1초
- [ ] 이미지 최적화 (lazy loading)

### 7.2 접근성
- [ ] 키보드 네비게이션 지원
- [ ] ARIA 레이블 추가
- [ ] 색상 대비 4.5:1 이상
- [ ] 포커스 인디케이터 명확

### 7.3 브라우저 호환성
- Chrome (최신 2버전)
- Safari (최신 2버전)
- Firefox (최신 2버전)
- Edge (최신 2버전)

### 7.4 보안
- [ ] CSRF 토큰 모든 폼에 포함
- [ ] XSS 방지 (v-html 사용 최소화)
- [ ] 민감 정보 로컬 스토리지 암호화
- [ ] API 토큰 안전한 관리

---

## 8. 개발 프로세스

### 8.1 작업 흐름
1. **디자인 확인** → 2. **컴포넌트 분해** → 3. **API 연동** → 4. **테스트** → 5. **리뷰** → 6. **배포**

### 8.2 브랜치 전략
```
main
├── develop
    ├── feature/meal-logging
    ├── feature/exercise-logging
    ├── feature/weight-tracking
    └── feature/diet-plan-viewer
```

### 8.3 커밋 컨벤션
```
feat: 새로운 기능
fix: 버그 수정
style: UI/스타일 변경
refactor: 코드 리팩토링
docs: 문서 수정
test: 테스트 추가/수정
```

### 8.4 테스트 체크리스트
- [ ] 기능 테스트 (모든 사용자 플로우)
- [ ] 반응형 테스트 (모바일/태블릿/데스크톱)
- [ ] 브라우저 테스트
- [ ] API 에러 처리 테스트
- [ ] 로딩 상태 테스트

---

## 9. 마일스톤

### Milestone 1: 핵심 기능 완성 (Week 1-2)
- [ ] 식사 기록 CRUD
- [ ] 운동 기록 CRUD
- [ ] 체중 기록 CRUD
- [ ] 모든 기능 모바일 반응형

**완료 조건**:
- 사용자가 일일 활동을 모두 기록 가능
- 대시보드에서 오늘의 요약 확인 가능

### Milestone 2: 확장 기능 (Week 3)
- [ ] 다이어트 플랜 뷰어
- [ ] 플랜 대체 기능
- [ ] 주간 요약
- [ ] 차트 시각화

**완료 조건**:
- AI 플랜 확인 및 수정 가능
- 진행 상황 차트로 확인 가능

### Milestone 3: 완성도 향상 (Week 4)
- [ ] 프로필 관리
- [ ] 설문 페이지
- [ ] 알림 기능
- [ ] 성능 최적화

**완료 조건**:
- 모든 기능 완전 작동
- 성능 요구사항 충족

---

## 10. 위험 요소 및 대응

### 10.1 기술적 위험
| 위험 | 영향 | 확률 | 대응 방안 |
|------|------|------|-----------|
| API 응답 지연 | 높음 | 중간 | 로딩 인디케이터, 캐싱 |
| 차트 렌더링 성능 | 중간 | 낮음 | 데이터 페이지네이션, lazy loading |
| 모바일 반응형 이슈 | 높음 | 중간 | 모바일 우선 개발, 지속적 테스트 |

### 10.2 일정적 위험
| 위험 | 영향 | 확률 | 대응 방안 |
|------|------|------|-----------|
| 예상 시간 초과 | 중간 | 높음 | 우선순위 조정, MVP 먼저 완성 |
| 범위 증가 | 높음 | 중간 | Phase 구분, 핵심 기능 먼저 |

---

## 11. 다음 단계

### 즉시 시작
1. ✅ PRD 검토 및 승인
2. ⏳ Milestone 1 시작:
   - Task 1: 식사 목록 페이지
   - Task 2: 식사 추가 페이지
   - Task 3: 운동 목록 페이지
   - Task 4: 운동 추가 페이지
   - Task 5: 체중 기록 페이지

### 권장 작업 순서
```
1. 식사 목록 페이지 (4h)
   ↓
2. 식사 추가 페이지 (6h)
   ↓
3. 운동 목록 페이지 (4h)
   ↓
4. 운동 추가 페이지 (6h)
   ↓
5. 체중 기록 페이지 (5h)
   ↓
6. 통합 테스트 (3h)
```

---

## 부록

### A. API 엔드포인트 매핑
[별도 문서 참조: API_DOCUMENTATION.md]

### B. 디자인 시스템
[별도 문서 참조: DESIGN_SYSTEM.md]

### C. 컴포넌트 라이브러리
[구현 예정]

---

**문서 승인**:
- [ ] 개발팀 리드
- [ ] 프로덕트 매니저
- [ ] 디자이너

**변경 이력**:
- 2025-12-05: v1.0 초안 작성
