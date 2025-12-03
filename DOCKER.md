# Docker 환경 설정 가이드

## 구성

이 프로젝트는 다음 Docker 서비스로 구성되어 있습니다:

- **PHP 8.4 + Apache**: Laravel 애플리케이션 실행
- **MySQL 8.0**: 데이터베이스
- **Redis**: 캐시, 세션, 큐 관리

## 필수 요구사항

- Docker
- Docker Compose

## 시작하기

### 1. 환경 설정

처음 실행 시 `.env` 파일을 생성합니다:

```bash
cp .env.example .env
```

### 2. Docker 컨테이너 실행

```bash
# 컨테이너 빌드 및 실행
docker-compose up -d

# 또는 백그라운드 로그 확인
docker-compose up
```

### 3. Composer 의존성 설치

```bash
docker-compose exec app composer install
```

### 4. Application Key 생성

```bash
docker-compose exec app php artisan key:generate
```

### 5. 데이터베이스 마이그레이션

```bash
docker-compose exec app php artisan migrate
```

### 6. 애플리케이션 접속

브라우저에서 http://localhost:8000 으로 접속

## 주요 명령어

### 컨테이너 관리

```bash
# 컨테이너 시작
docker-compose up -d

# 컨테이너 중지
docker-compose down

# 컨테이너 재시작
docker-compose restart

# 로그 확인
docker-compose logs -f

# 특정 서비스 로그 확인
docker-compose logs -f app
```

### Laravel 명령어 실행

```bash
# Artisan 명령어
docker-compose exec app php artisan [command]

# Composer 명령어
docker-compose exec app composer [command]

# 컨테이너 접속
docker-compose exec app bash
```

### 데이터베이스 관리

```bash
# MySQL 접속
docker-compose exec mysql mysql -u myfit_user -p
# 비밀번호: myfit_password

# 데이터베이스 백업
docker-compose exec mysql mysqldump -u myfit_user -pmyfit_password myfit > backup.sql

# 데이터베이스 복원
docker-compose exec -T mysql mysql -u myfit_user -pmyfit_password myfit < backup.sql
```

### Redis 관리

```bash
# Redis CLI 접속
docker-compose exec redis redis-cli

# 캐시 클리어
docker-compose exec app php artisan cache:clear
```

## 포트 설정

- **애플리케이션**: http://localhost:8000
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

## 볼륨 관리

데이터베이스와 Redis 데이터는 Docker 볼륨에 저장됩니다:

```bash
# 볼륨 목록 확인
docker volume ls

# 볼륨 삭제 (주의: 데이터가 모두 삭제됩니다)
docker-compose down -v
```

## 트러블슈팅

### 권한 문제

```bash
# storage 및 bootstrap/cache 권한 설정
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### 컨테이너 재빌드

```bash
# 캐시 없이 재빌드
docker-compose build --no-cache

# 컨테이너 재생성
docker-compose up -d --force-recreate
```

### 포트 충돌

기본 포트가 이미 사용 중인 경우 `docker-compose.yml`에서 포트를 변경하세요:

```yaml
ports:
  - "8080:80"  # 8000 대신 8080 사용
```
