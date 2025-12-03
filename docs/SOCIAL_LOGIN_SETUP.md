# 소셜 로그인 설정 가이드

## 1. Laravel Socialite 설치

```bash
composer require laravel/socialite
composer require socialiteproviders/kakao
```

## 2. 환경 변수 설정 (.env)

```env
# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# Kakao OAuth
KAKAO_CLIENT_ID=your-kakao-rest-api-key
KAKAO_CLIENT_SECRET=your-kakao-client-secret
KAKAO_REDIRECT_URI=http://localhost:8000/api/auth/kakao/callback

# Frontend URL (for password reset emails)
APP_FRONTEND_URL=http://localhost:3000
```

## 3. config/services.php 설정

```php
return [
    // ... existing services

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'kakao' => [
        'client_id' => env('KAKAO_CLIENT_ID'),
        'client_secret' => env('KAKAO_CLIENT_SECRET'),
        'redirect' => env('KAKAO_REDIRECT_URI'),
    ],
];
```

## 4. Kakao Provider 등록

`config/app.php`의 `providers` 배열에 추가:

```php
'providers' => [
    // ...
    \SocialiteProviders\Manager\ServiceProvider::class,
],
```

`app/Providers/EventServiceProvider.php`에 추가:

```php
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Kakao\KakaoExtendSocialite;

protected $listen = [
    SocialiteWasCalled::class => [
        KakaoExtendSocialite::class,
    ],
];
```

## 5. Google OAuth 설정

### 5.1 Google Cloud Console에서 프로젝트 생성
1. [Google Cloud Console](https://console.cloud.google.com/) 접속
2. 새 프로젝트 생성
3. "API 및 서비스" > "OAuth 동의 화면" 설정
4. "API 및 서비스" > "사용자 인증 정보" > "OAuth 2.0 클라이언트 ID" 생성

### 5.2 승인된 리디렉션 URI 추가
```
http://localhost:8000/api/auth/google/callback
https://yourdomain.com/api/auth/google/callback
```

### 5.3 필요한 범위 (Scopes)
- `email`
- `profile`

## 6. Kakao OAuth 설정

### 6.1 Kakao Developers에서 앱 생성
1. [Kakao Developers](https://developers.kakao.com/) 접속
2. "내 애플리케이션" > "애플리케이션 추가하기"
3. 앱 생성 후 "REST API 키" 확인 (KAKAO_CLIENT_ID)

### 6.2 플랫폼 설정
"내 애플리케이션" > "앱 설정" > "플랫폼"
- Web: `http://localhost:8000`, `https://yourdomain.com`

### 6.3 Redirect URI 설정
"내 애플리케이션" > "제품 설정" > "카카오 로그인"
- Redirect URI 추가:
  ```
  http://localhost:8000/api/auth/kakao/callback
  https://yourdomain.com/api/auth/kakao/callback
  ```

### 6.4 동의 항목 설정
"제품 설정" > "카카오 로그인" > "동의 항목"
- 필수 동의: 닉네임, 프로필 사진
- 선택 동의: 카카오계정(이메일)

## 7. API 엔드포인트

### 7.1 OAuth 리디렉션 URL 가져오기
```
GET /api/auth/{provider}/redirect

provider: google | kakao

Response:
{
  "success": true,
  "data": {
    "redirect_url": "https://accounts.google.com/o/oauth2/auth?..."
  }
}
```

### 7.2 OAuth 콜백 처리
```
POST /api/auth/{provider}/callback

Body:
{
  "code": "authorization_code_from_oauth"
}

Response:
{
  "success": true,
  "message": "소셜 로그인에 성공했습니다.",
  "data": {
    "user": {
      "id": 1,
      "name": "홍길동",
      "email": "user@example.com",
      "profile_photo_path": "https://..."
    },
    "token": "1|abc123..."
  }
}
```

### 7.3 연동된 소셜 계정 조회 (인증 필요)
```
GET /api/auth/social/accounts
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "accounts": [
      {
        "provider": "google",
        "provider_name": "Google",
        "connected_at": "2025-12-03T13:00:00Z"
      }
    ]
  }
}
```

### 7.4 소셜 계정 연동 해제 (인증 필요)
```
DELETE /api/auth/social/{provider}
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "소셜 계정 연동이 해제되었습니다."
}
```

## 8. 프론트엔드 통합 가이드

### 8.1 소셜 로그인 플로우

```javascript
// 1. 리디렉션 URL 가져오기
const response = await fetch('/api/auth/google/redirect');
const { data } = await response.json();

// 2. 사용자를 OAuth 페이지로 리디렉션
window.location.href = data.redirect_url;

// 3. 콜백 URL에서 code 파라미터 받기
const urlParams = new URLSearchParams(window.location.search);
const code = urlParams.get('code');

// 4. 백엔드로 code 전송
const authResponse = await fetch('/api/auth/google/callback', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ code })
});

const { data: { token } } = await authResponse.json();

// 5. 토큰 저장 및 사용자 인증
localStorage.setItem('token', token);
```

## 9. 보안 고려사항

### 9.1 토큰 저장
- API 토큰은 httpOnly 쿠키 또는 secure storage에 저장
- LocalStorage는 XSS 공격에 취약하므로 주의

### 9.2 CSRF 보호
- Socialite는 state 파라미터를 통해 CSRF 보호 제공
- `stateless()` 사용 시 수동 검증 필요

### 9.3 이메일 미제공 대응
- Kakao는 이메일을 선택 동의로 제공
- 이메일이 없는 경우 임시 이메일 생성: `{provider_id}@kakao.temp`
- 사용자에게 이메일 입력 요청 UI 구현 권장

## 10. 테스트

```bash
# Feature Tests 실행
php artisan test --filter=SocialAuthTest

# 특정 테스트만 실행
php artisan test --filter=test_google_callback_creates_new_user
```

## 11. 트러블슈팅

### 문제: "Invalid redirect URI"
**해결**: OAuth 콘솔에서 Redirect URI가 정확히 일치하는지 확인

### 문제: Kakao 이메일을 받지 못함
**해결**: Kakao Developers에서 "동의 항목"에서 이메일 동의 설정 확인

### 문제: 토큰 만료 에러
**해결**: Socialite는 access_token 자동 갱신 안 함. refresh_token 로직 별도 구현 필요

## 12. 운영 환경 체크리스트

- [ ] .env 파일에 실제 OAuth 키 설정
- [ ] config/services.php에 설정 확인
- [ ] Google Cloud Console에 운영 도메인 등록
- [ ] Kakao Developers에 운영 도메인 등록
- [ ] HTTPS 적용 확인
- [ ] 프론트엔드 URL 설정 (APP_FRONTEND_URL)
- [ ] 이메일 알림 큐 설정 (Redis, Database 등)
