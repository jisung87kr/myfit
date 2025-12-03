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
