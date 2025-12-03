# MyFit 아키텍처 가이드

## 개요

MyFit 프로젝트는 **레이어드 아키텍처(Layered Architecture)** 패턴을 따릅니다.
각 레이어는 명확한 책임을 가지며, 비즈니스 로직은 Service Layer로 분리됩니다.

---

## 아키텍처 다이어그램

```
┌─────────────────────────────────────────────────────────┐
│                    HTTP Request                         │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│              Controller Layer                           │
│  - HTTP 요청/응답 처리                                    │
│  - Request Validation (Form Request)                    │
│  - Service 호출                                          │
│  - 예외 처리 및 응답 반환                                  │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│               Service Layer                             │
│  - 비즈니스 로직 구현                                      │
│  - 트랜잭션 관리                                          │
│  - 여러 모델/리포지토리 조합                                │
│  - 외부 API 호출 (GPT, 결제 등)                           │
│  - 이벤트 발생                                            │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│          Repository Layer (Optional)                    │
│  - 데이터베이스 쿼리                                       │
│  - 복잡한 조회 로직                                        │
│  - 데이터 CRUD                                           │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│                 Model Layer                             │
│  - 데이터베이스 테이블 매핑                                 │
│  - 관계(Relationships) 정의                              │
│  - Accessor & Mutator                                   │
│  - Model Events                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 레이어별 상세 설명

### 1. Controller Layer (컨트롤러)

**위치**: `app/Http/Controllers/Api/`

**책임**:
- HTTP 요청 수신 및 응답 반환
- Request Validation (Form Request 클래스 사용)
- Service Layer 호출
- 예외 처리 및 에러 응답

**금지사항**:
- ❌ 비즈니스 로직 작성 (User 생성, 토큰 발급 등)
- ❌ 직접 데이터베이스 접근 (User::create() 등)
- ❌ 복잡한 데이터 가공

**예시**:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $result = $this->authService->register($validated);

        return response()->json([
            'success' => true,
            'message' => '회원가입이 완료되었습니다.',
            'data' => $result,
        ], 201);
    }
}
```

**Response 매크로 사용**:

컨트롤러에서는 표준화된 응답 매크로를 사용합니다:

```php
// ✅ Good - Response 매크로 사용
public function register(RegisterRequest $request)
{
    $result = $this->authService->register($request->validated());
    return response()->created($result, '회원가입이 완료되었습니다.');
}

public function login(LoginRequest $request)
{
    $result = $this->authService->login($request->email, $request->password);
    return response()->success($result, '로그인 성공');
}

public function logout(Request $request)
{
    $this->authService->logout($request->user());
    return response()->success(null, '로그아웃되었습니다.');
}

// 에러 응답
public function someMethod()
{
    return response()->error('오류 메시지', $errors, 400);
    return response()->validationError($errors, '입력값을 확인해주세요.');
    return response()->unauthorized('인증이 필요합니다.');
    return response()->forbidden('권한이 없습니다.');
    return response()->notFound('리소스를 찾을 수 없습니다.');
}
```

**사용 가능한 Response 매크로**:

| 매크로 | 설명 | HTTP 코드 |
|-------|------|----------|
| `response()->success($data, $message, $code)` | 성공 응답 | 200 (기본) |
| `response()->created($data, $message)` | 생성 성공 | 201 |
| `response()->noContent()` | 내용 없음 | 204 |
| `response()->error($message, $errors, $code)` | 에러 응답 | 400 (기본) |
| `response()->validationError($errors, $message)` | 검증 실패 | 422 |
| `response()->unauthorized($message)` | 인증 필요 | 401 |
| `response()->forbidden($message)` | 권한 없음 | 403 |
| `response()->notFound($message)` | 찾을 수 없음 | 404 |

**응답 형식**:

성공 응답:
```json
{
    "success": true,
    "message": "메시지 (선택)",
    "data": { ... }
}
```

에러 응답:
```json
{
    "success": false,
    "message": "에러 메시지",
    "errors": { ... }
}
```

---

### 2. Service Layer (서비스)

**위치**: `app/Services/`

**명명 규칙**: `{Domain}Service.php`
- `AuthService.php` - 인증 관련
- `SurveyService.php` - 설문 관련
- `DietPlanService.php` - 다이어트 플랜 관련
- `UserService.php` - 사용자 관련

**책임**:
- 모든 비즈니스 로직 구현
- 데이터베이스 트랜잭션 관리
- 여러 모델 조합 및 조율
- 외부 API 호출 (OpenAI GPT, 결제 API 등)
- 이벤트 발생 (UserRegistered, PlanGenerated 등)
- 복잡한 데이터 가공 및 계산

**예시**:

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Authenticate user
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new AuthenticationException('인증 실패');
        }

        // 기존 토큰 삭제
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
```

---

### 3. Repository Layer (리포지토리 - 선택사항)

**위치**: `app/Repositories/`

**사용 시기**:
- 복잡한 쿼리가 많은 경우
- 동일한 쿼리를 여러 곳에서 재사용하는 경우
- 테스트 용이성이 중요한 경우 (Mock 객체 사용)

**책임**:
- 데이터베이스 쿼리 작성
- 데이터 CRUD 작업
- 복잡한 조회 로직

**예시**:

```php
<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function getActiveUsers()
    {
        return User::whereNotNull('email_verified_at')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();
    }
}
```

---

### 4. Model Layer (모델)

**위치**: `app/Models/`

**책임**:
- 데이터베이스 테이블과 1:1 매핑
- 관계(Relationships) 정의
- Accessor & Mutator (데이터 변환)
- Model Events (creating, created, updating, updated 등)
- Scopes (쿼리 재사용)

**금지사항**:
- ❌ 비즈니스 로직
- ❌ HTTP 관련 코드
- ❌ 외부 API 호출

**예시**:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 관계 정의
    public function dietPlans()
    {
        return $this->hasMany(DietPlan::class);
    }

    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    // Accessor
    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : null;
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }
}
```

---

## Request Validation (Form Request)

컨트롤러에서 직접 validation하지 않고, **Form Request** 클래스를 사용합니다.

**위치**: `app/Http/Requests/`

**생성 방법**:
```bash
php artisan make:request Auth/RegisterRequest
```

**예시**:

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => '이미 사용 중인 이메일입니다.',
            'password.regex' => '비밀번호는 영문과 숫자를 포함해야 합니다.',
        ];
    }
}
```

**사용 예시**:

```php
public function register(RegisterRequest $request)
{
    // $request->validated()는 이미 검증된 데이터
    $result = $this->authService->register($request->validated());

    return response()->json(['data' => $result], 201);
}
```

---

## 의존성 주입 (Dependency Injection)

Service는 **생성자 주입(Constructor Injection)** 을 사용합니다.

```php
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        // $this->authService 사용
        $result = $this->authService->register($request->validated());
        return response()->json($result);
    }
}
```

### Service에서 다른 Service 주입

```php
class DietPlanService
{
    public function __construct(
        private SurveyService $surveyService,
        private OpenAIService $openAIService
    ) {}

    public function generatePlan(int $userId): DietPlan
    {
        // 다른 서비스 사용
        $survey = $this->surveyService->getUserSurvey($userId);
        $plan = $this->openAIService->generateDietPlan($survey);

        return DietPlan::create([...]);
    }
}
```

---

## 디렉토리 구조

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── SurveyController.php
│   │       ├── DietPlanController.php
│   │       └── ProfileController.php
│   │
│   └── Requests/
│       ├── Auth/
│       │   ├── RegisterRequest.php
│       │   ├── LoginRequest.php
│       │   └── ChangePasswordRequest.php
│       │
│       ├── Survey/
│       │   └── SubmitSurveyRequest.php
│       │
│       └── DietPlan/
│           └── GeneratePlanRequest.php
│
├── Services/
│   ├── AuthService.php
│   ├── SurveyService.php
│   ├── DietPlanService.php
│   ├── UserService.php
│   └── OpenAIService.php
│
├── Repositories/ (선택)
│   ├── UserRepository.php
│   ├── SurveyRepository.php
│   └── DietPlanRepository.php
│
└── Models/
    ├── User.php
    ├── Survey.php
    ├── SurveyQuestion.php
    ├── UserSurveyResponse.php
    ├── DietPlan.php
    └── MealPlan.php
```

---

## 트랜잭션 관리

복잡한 비즈니스 로직에서 **DB 트랜잭션**을 사용합니다.

```php
use Illuminate\Support\Facades\DB;

class DietPlanService
{
    public function generatePlan(int $userId): DietPlan
    {
        DB::beginTransaction();

        try {
            // 여러 단계의 데이터 생성
            $plan = DietPlan::create([...]);

            foreach ($meals as $meal) {
                MealPlan::create([...]);
            }

            DB::commit();

            return $plan;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

---

## 예외 처리

### Service에서 예외 발생

```php
use Illuminate\Auth\AuthenticationException;

public function login(string $email, string $password): array
{
    $user = User::where('email', $email)->first();

    if (!$user || !Hash::check($password, $user->password)) {
        throw new AuthenticationException('인증 실패');
    }

    // ...
}
```

### Controller에서 예외 처리

```php
use Illuminate\Validation\ValidationException;

public function login(LoginRequest $request)
{
    try {
        $result = $this->authService->login(
            $request->email,
            $request->password
        );

        return response()->json(['data' => $result]);
    } catch (AuthenticationException $e) {
        throw ValidationException::withMessages([
            'email' => [$e->getMessage()],
        ]);
    }
}
```

---

## Enum 활용

Enum을 사용하여 상수값을 타입 안전하게 관리합니다.

### HttpStatus Enum

HTTP 상태 코드를 Enum으로 관리:

```php
use App\Enums\HttpStatus;

// ApiResponse에서 사용
return ApiResponse::success($data, $message, HttpStatus::CREATED);
return ApiResponse::error($message, $errors, HttpStatus::NOT_FOUND);

// Enum 메서드 활용
$status = HttpStatus::OK;
$status->isSuccess(); // true
$status->description(); // "요청이 성공적으로 처리되었습니다."
```

### SocialProvider Enum

소셜 로그인 제공자 관리:

```php
use App\Enums\SocialProvider;

// Model cast로 사용
class SocialAccount extends Model
{
    protected function casts(): array
    {
        return [
            'provider' => SocialProvider::class,
        ];
    }
}

// 사용 예시
$account->provider = SocialProvider::GOOGLE;
$account->provider->displayName(); // "Google"
$account->provider->isEnabled(); // true/false
```

### TokenType Enum

토큰 타입 및 만료 시간 관리:

```php
use App\Enums\TokenType;

// 토큰 생성 시
$token = $user->createToken(TokenType::AUTH->value)->plainTextToken;

// 토큰 정보 조회
TokenType::AUTH->expiresIn(); // 1440 (minutes)
TokenType::PASSWORD_RESET->description(); // "비밀번호 재설정 토큰"
```

### CacheKey Enum

캐시 키 관리:

```php
use App\Enums\CacheKey;

// 캐시 저장
Cache::put(
    CacheKey::USER_PROFILE->key($userId),
    $userData,
    CacheKey::USER_PROFILE->ttl()
);

// 캐시 조회
$cached = Cache::get(CacheKey::USER_PROFILE->key($userId));
```

### Enum 생성 가이드

새로운 Enum이 필요한 경우:

1. **위치**: `app/Enums/`
2. **명명**: `{Domain}Type`, `{Domain}Status` 등
3. **타입**: `string` 또는 `int` backed enum 사용
4. **메서드**: 비즈니스 로직 관련 헬퍼 메서드 추가

```php
enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function canCancel(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => '주문 대기',
            self::CONFIRMED => '주문 확인',
            self::SHIPPED => '배송 중',
            self::DELIVERED => '배송 완료',
            self::CANCELLED => '주문 취소',
        };
    }
}
```

---

## 디자인 패턴

### 1. Repository Pattern (선택사항)

복잡한 쿼리를 캡슐화하고 데이터 접근 로직을 분리합니다.

**언제 사용?**
- 복잡한 쿼리가 여러 곳에서 재사용될 때
- 데이터 소스 변경 가능성이 있을 때
- 테스트 용이성을 높이고 싶을 때

**구현 예시**:

```php
// app/Repositories/UserRepository.php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findActiveUsers(): Collection
    {
        return User::where('is_active', true)
            ->whereNotNull('email_verified_at')
            ->get();
    }

    public function findUsersWithExpiredPlans(): Collection
    {
        return User::whereHas('dietPlan', function ($query) {
            $query->where('expires_at', '<', now());
        })->get();
    }
}

// Service에서 사용
class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function notifyUsersWithExpiredPlans(): void
    {
        $users = $this->userRepository->findUsersWithExpiredPlans();

        foreach ($users as $user) {
            // 알림 로직
        }
    }
}
```

### 2. Strategy Pattern

알고리즘을 캡슐화하여 런타임에 교체 가능하게 합니다.

**사용 예시: 소셜 로그인**

```php
// app/Services/Auth/SocialAuthStrategy.php
interface SocialAuthStrategy
{
    public function authenticate(string $token): User;
}

// Google 전략
class GoogleAuthStrategy implements SocialAuthStrategy
{
    public function authenticate(string $token): User
    {
        // Google OAuth 처리
        $googleUser = Socialite::driver('google')->userFromToken($token);

        return $this->findOrCreateUser($googleUser);
    }
}

// Kakao 전략
class KakaoAuthStrategy implements SocialAuthStrategy
{
    public function authenticate(string $token): User
    {
        // Kakao OAuth 처리
        $kakaoUser = Socialite::driver('kakao')->userFromToken($token);

        return $this->findOrCreateUser($kakaoUser);
    }
}

// Context
class SocialAuthService
{
    public function authenticate(SocialProvider $provider, string $token): User
    {
        $strategy = match($provider) {
            SocialProvider::GOOGLE => new GoogleAuthStrategy(),
            SocialProvider::KAKAO => new KakaoAuthStrategy(),
            SocialProvider::NAVER => new NaverAuthStrategy(),
        };

        return $strategy->authenticate($token);
    }
}
```

### 3. Factory Pattern

객체 생성 로직을 캡슐화합니다.

**사용 예시: 알림 생성**

```php
// app/Factories/NotificationFactory.php
enum NotificationType: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case PUSH = 'push';
}

class NotificationFactory
{
    public static function create(NotificationType $type): NotificationChannel
    {
        return match($type) {
            NotificationType::EMAIL => new EmailNotification(),
            NotificationType::SMS => new SmsNotification(),
            NotificationType::PUSH => new PushNotification(),
        };
    }
}

// 사용
$notification = NotificationFactory::create(NotificationType::EMAIL);
$notification->send($user, $message);
```

### 4. Observer Pattern (Laravel Events)

이벤트 기반 아키텍처를 통해 느슨한 결합을 유지합니다.

```php
// app/Events/UserRegistered.php
class UserRegistered
{
    public function __construct(public User $user) {}
}

// app/Listeners/SendWelcomeEmail.php
class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
}

// Service에서 이벤트 발생
public function register(array $data): User
{
    $user = User::create($data);

    event(new UserRegistered($user));

    return $user;
}
```

### 5. Decorator Pattern

객체에 동적으로 기능을 추가합니다.

**사용 예시: 캐시 데코레이터**

```php
interface DietPlanService
{
    public function getPlan(int $userId): DietPlan;
}

class BaseDietPlanService implements DietPlanService
{
    public function getPlan(int $userId): DietPlan
    {
        return DietPlan::where('user_id', $userId)->latest()->first();
    }
}

class CachedDietPlanService implements DietPlanService
{
    public function __construct(
        private DietPlanService $service
    ) {}

    public function getPlan(int $userId): DietPlan
    {
        return Cache::remember(
            CacheKey::USER_DIET_PLAN->key($userId),
            CacheKey::USER_DIET_PLAN->ttl(),
            fn() => $this->service->getPlan($userId)
        );
    }
}
```

### 패턴 선택 가이드

| 상황 | 추천 패턴 |
|------|----------|
| 복잡한 쿼리 재사용 | Repository |
| 알고리즘이 여러 개 필요 | Strategy |
| 객체 생성이 복잡함 | Factory |
| 이벤트 기반 처리 | Observer (Laravel Events) |
| 기능 추가/확장 | Decorator |

---

## 체크리스트

새로운 기능 구현 시 다음을 확인하세요:

### Controller
- [ ] HTTP 요청/응답만 처리하는가?
- [ ] Form Request를 사용하여 validation을 분리했는가?
- [ ] 비즈니스 로직을 Service에 위임했는가?
- [ ] 의존성 주입을 사용했는가?

### Service
- [ ] 비즈니스 로직이 Service에 있는가?
- [ ] 트랜잭션이 필요한 경우 DB::transaction을 사용했는가?
- [ ] 적절한 예외를 던지는가?
- [ ] 다른 Service와의 의존성을 생성자 주입으로 처리했는가?

### Model
- [ ] 데이터베이스 구조와 1:1 매핑되는가?
- [ ] 관계가 올바르게 정의되어 있는가?
- [ ] 비즈니스 로직이 Model에 없는가?

### Tests
- [ ] Feature Test를 작성했는가?
- [ ] Service 단위 테스트를 작성했는가?
- [ ] 테스트 커버리지가 80% 이상인가?

---

## 참고 자료

- [Laravel Service Pattern](https://laravel.com)
- [Repository Pattern in Laravel](https://laravel.com)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
