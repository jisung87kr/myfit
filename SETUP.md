# MyFit 프로젝트 설치 가이드

## 필수 요구사항

- Docker
- Docker Compose

## 설치 방법

### 1. Docker 컨테이너 시작

```bash
docker compose up -d
```

또는 Makefile 사용:

```bash
make up
```

### 2. Composer 의존성 설치

```bash
docker compose exec app composer install
```

또는:

```bash
make composer-install
```

### 3. 데이터베이스 마이그레이션

```bash
docker compose exec app php artisan migrate
```

또는:

```bash
make migrate
```

마이그레이션 완료 시 다음 테이블들이 생성됩니다:

#### Laravel 기본 테이블
- `users` - 사용자 정보
- `password_reset_tokens` - 비밀번호 재설정
- `sessions` - 세션 정보
- `cache` - 캐시 데이터
- `cache_locks` - 캐시 락
- `jobs` - 큐 작업
- `job_batches` - 배치 작업
- `failed_jobs` - 실패한 작업

#### Laravel Sanctum 테이블
- `personal_access_tokens` - API 토큰

#### Spatie Permission 테이블
- `permissions` - 권한 목록
- `roles` - 역할 목록
- `model_has_permissions` - 모델별 권한
- `model_has_roles` - 모델별 역할
- `role_has_permissions` - 역할별 권한

### 4. 애플리케이션 접속

브라우저에서 http://localhost:8000 으로 접속

### 5. Horizon 대시보드

Redis 큐 모니터링: http://localhost:8000/horizon

## 빠른 설치 (한 번에 모두 실행)

```bash
make setup
```

이 명령어는 다음을 자동으로 실행합니다:
1. .env 파일 생성 (없는 경우)
2. Docker 컨테이너 시작
3. Composer 의존성 설치
4. Application key 생성
5. 데이터베이스 마이그레이션

## 주요 명령어

### Docker 관리
```bash
make up              # 컨테이너 시작
make down            # 컨테이너 중지
make restart         # 컨테이너 재시작
make logs            # 로그 확인
make shell           # 컨테이너 접속
```

### 데이터베이스
```bash
make migrate         # 마이그레이션 실행
make fresh           # 데이터베이스 초기화 후 마이그레이션
make seed            # 시더 실행
make mysql           # MySQL 콘솔 접속
```

### Laravel
```bash
make cache-clear     # 모든 캐시 클리어
make optimize        # 애플리케이션 최적화
make test            # 테스트 실행
```

### Horizon
```bash
docker compose exec app php artisan horizon
```

또는:

```bash
make artisan cmd="horizon"
```

## 트러블슈팅

### 포트 충돌
기본 포트(8000, 3306, 6379)가 이미 사용 중인 경우 `docker-compose.yml`에서 포트를 변경하세요.

### 권한 문제
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### 마이그레이션 오류
```bash
# 데이터베이스 초기화
make fresh

# 또는 컨테이너 재시작
make restart
```

## 개발 환경 정보

- **PHP**: 8.4
- **Laravel**: 12.39
- **MySQL**: 8.0
- **Redis**: Alpine
- **Web Server**: Apache

## 설치된 주요 패키지

- Laravel Sanctum 4.2 - API 인증
- Laravel Horizon 5.40 - 큐 모니터링
- Spatie Laravel Permission 6.23 - 권한 관리
- Laravel Pint - 코드 스타일
- PHPUnit - 테스팅

## API 사용 예시

### 토큰 생성
```php
$user = User::find(1);
$token = $user->createToken('api-token')->plainTextToken;
```

### API 요청
```bash
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer {your-token}"
```

### 권한 확인
```php
// 권한 체크
if ($user->can('edit articles')) {
    // ...
}

// 역할 체크
if ($user->hasRole('admin')) {
    // ...
}
```

## 프로덕션 배포

프로덕션 환경으로 배포하기 전:

1. `.env` 파일 설정 변경
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - 강력한 비밀번호 설정

2. 캐시 최적화
```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

3. 자산 빌드
```bash
docker compose exec app npm run build
```
