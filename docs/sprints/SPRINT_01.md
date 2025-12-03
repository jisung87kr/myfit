# Sprint 1: 사용자 인증 시스템

## Epic 1.1: 사용자 인증 시스템
**기간**: Week 2-3
**우선순위**: CRITICAL
**Story Points**: 16

---

## Sprint 목표

사용자가 회원가입하고 로그인하여 API 토큰을 받아 인증된 요청을 할 수 있는 기반 구축

---

## Sub-Epics

### ✅ Sub-Epic 1.1.1: 이메일 회원가입/로그인 (5pt)

**User Stories**
- [ ] 사용자로서, 이메일과 비밀번호로 회원가입할 수 있다
- [ ] 사용자로서, 이메일 중복 확인을 받을 수 있다
- [ ] 사용자로서, 비밀번호 강도를 확인받을 수 있다
- [ ] 사용자로서, 로그인하여 API 토큰을 받을 수 있다
- [ ] 사용자로서, 로그아웃할 수 있다

**Tasks**
- [ ] Users 테이블 마이그레이션 확장
- [ ] AuthController 생성
- [ ] 회원가입 API 구현 (POST /api/register)
- [ ] 로그인 API 구현 (POST /api/login)
- [ ] 로그아웃 API 구현 (POST /api/logout)
- [ ] 사용자 정보 조회 (GET /api/user)
- [ ] Validation Rules 작성
- [ ] Feature Tests 작성
- [ ] API 문서화

**API Endpoints**
```
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/user
```

**완료 조건**
- [ ] 회원가입 시 토큰 발급
- [ ] 로그인 시 토큰 발급
- [ ] 토큰으로 인증된 요청 가능
- [ ] 이메일 중복 검사
- [ ] 비밀번호 최소 8자, 영문+숫자 조합
- [ ] 테스트 커버리지 80% 이상

---

### Sub-Epic 1.1.2: 프로필 관리 (3pt)

**User Stories**
- [ ] 사용자로서, 내 프로필 정보를 조회할 수 있다
- [ ] 사용자로서, 이름을 수정할 수 있다
- [ ] 사용자로서, 비밀번호를 변경할 수 있다
- [ ] 사용자로서, 프로필 사진을 업로드할 수 있다

**Tasks**
- [ ] ProfileController 생성
- [ ] 프로필 조회 API (GET /api/user/profile)
- [ ] 프로필 수정 API (PUT /api/user/profile)
- [ ] 비밀번호 변경 API (PUT /api/user/password)
- [ ] 프로필 사진 업로드 (POST /api/user/profile/photo)
- [ ] 이미지 저장 (Storage)
- [ ] Feature Tests 작성

**API Endpoints**
```
GET    /api/user/profile
PUT    /api/user/profile
POST   /api/user/profile/photo
PUT    /api/user/password
```

**완료 조건**
- [ ] 프로필 CRUD 동작
- [ ] 비밀번호 변경 시 기존 비밀번호 확인
- [ ] 이미지 업로드 및 저장 (max 2MB)
- [ ] 테스트 작성

---

### Sub-Epic 1.1.3: 소셜 로그인 (5pt)

**User Stories**
- [ ] 사용자로서, Google 계정으로 간편 가입/로그인할 수 있다
- [ ] 사용자로서, Kakao 계정으로 간편 가입/로그인할 수 있다
- [ ] 사용자로서, 소셜 계정 연동을 해제할 수 있다

**Tasks**
- [ ] Socialite 패키지 설치
- [ ] social_accounts 마이그레이션 생성
- [ ] SocialAuthController 생성
- [ ] Google OAuth 설정
- [ ] Kakao OAuth 설정
- [ ] 소셜 로그인 콜백 처리
- [ ] 기존 이메일과 연동 로직
- [ ] 연동 해제 API
- [ ] Feature Tests 작성

**API Endpoints**
```
GET    /api/auth/google/redirect
GET    /api/auth/google/callback
GET    /api/auth/kakao/redirect
GET    /api/auth/kakao/callback
DELETE /api/auth/social/{provider}
```

**완료 조건**
- [ ] Google 로그인 동작
- [ ] Kakao 로그인 동작
- [ ] 기존 계정과 소셜 연동
- [ ] 테스트 작성

---

### Sub-Epic 1.1.4: 비밀번호 찾기/재설정 (3pt)

**User Stories**
- [ ] 사용자로서, 비밀번호를 잊었을 때 재설정 이메일을 받을 수 있다
- [ ] 사용자로서, 이메일 링크를 통해 새 비밀번호를 설정할 수 있다
- [ ] 사용자로서, 재설정 링크의 유효기간을 확인할 수 있다

**Tasks**
- [ ] PasswordResetController 생성
- [ ] 재설정 요청 API (POST /api/password/email)
- [ ] 비밀번호 재설정 API (POST /api/password/reset)
- [ ] 이메일 템플릿 작성
- [ ] 토큰 유효성 검증 (60분)
- [ ] Feature Tests 작성

**API Endpoints**
```
POST   /api/password/email
POST   /api/password/reset
```

**완료 조건**
- [ ] 재설정 이메일 발송
- [ ] 토큰 유효성 검증
- [ ] 비밀번호 재설정 완료
- [ ] 테스트 작성

---

## 데이터베이스 스키마

### users (확장)
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->nullable()->after('email');
    $table->string('profile_photo_path')->nullable();
    $table->timestamp('phone_verified_at')->nullable();
});
```

### social_accounts (신규)
```php
Schema::create('social_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('provider'); // google, kakao
    $table->string('provider_id');
    $table->string('provider_token')->nullable();
    $table->timestamps();

    $table->unique(['provider', 'provider_id']);
});
```

---

## API 응답 형식

### 성공 응답
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "..."
  },
  "message": "로그인 성공"
}
```

### 에러 응답
```json
{
  "success": false,
  "message": "이메일 또는 비밀번호가 올바르지 않습니다.",
  "errors": {
    "email": ["이메일 형식이 올바르지 않습니다."]
  }
}
```

---

## Validation Rules

### 회원가입
```php
[
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users',
    'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)/',
]
```

### 로그인
```php
[
    'email' => 'required|email',
    'password' => 'required|string',
]
```

### 프로필 수정
```php
[
    'name' => 'sometimes|string|max:255',
    'phone' => 'sometimes|nullable|string|max:20',
]
```

### 비밀번호 변경
```php
[
    'current_password' => 'required|string',
    'password' => 'required|string|min:8|confirmed|different:current_password',
]
```

---

## 테스트 시나리오

### AuthTest
- [ ] 회원가입 성공
- [ ] 회원가입 실패 (이메일 중복)
- [ ] 회원가입 실패 (약한 비밀번호)
- [ ] 로그인 성공
- [ ] 로그인 실패 (잘못된 비밀번호)
- [ ] 로그아웃 성공
- [ ] 인증된 사용자 정보 조회

### ProfileTest
- [ ] 프로필 조회 성공
- [ ] 프로필 수정 성공
- [ ] 비밀번호 변경 성공
- [ ] 비밀번호 변경 실패 (잘못된 현재 비밀번호)
- [ ] 프로필 사진 업로드 성공

### SocialAuthTest
- [ ] Google 로그인 리다이렉트
- [ ] Google 콜백 처리
- [ ] 기존 계정과 소셜 연동
- [ ] Kakao 로그인 리다이렉트
- [ ] Kakao 콜백 처리

### PasswordResetTest
- [ ] 재설정 이메일 발송
- [ ] 유효한 토큰으로 비밀번호 재설정
- [ ] 만료된 토큰으로 재설정 실패

---

## 보안 고려사항

- [ ] Rate Limiting (로그인: 5회/분)
- [ ] CSRF 보호
- [ ] SQL Injection 방지 (Eloquent ORM)
- [ ] XSS 방지 (입력값 검증)
- [ ] 비밀번호 해싱 (bcrypt)
- [ ] API 토큰 암호화
- [ ] HTTPS 사용 (프로덕션)

---

## 다음 단계

1. [ ] 마이그레이션 파일 생성
2. [ ] AuthController 구현
3. [ ] Validation Request 클래스 생성
4. [ ] Feature Tests 작성
5. [ ] API 문서화 (Postman Collection)

---

## 완료 체크리스트

- [ ] 모든 API 엔드포인트 동작
- [ ] 테스트 통과 (80% 이상)
- [ ] API 문서 작성
- [ ] 코드 리뷰 완료
- [ ] Git 커밋 및 푸시
