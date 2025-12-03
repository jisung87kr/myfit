# 칼로리 계산 엔진

MyFit의 칼로리 계산 시스템 문서입니다.

## 개요

사용자의 설문 데이터를 기반으로 BMR, TDEE, 목표 칼로리, 영양소 비율을 계산합니다.

## 계산 공식

### 1. BMR (Base Metabolic Rate) - Harris-Benedict 공식

**남성:**
```
BMR = 88.362 + (13.397 × 체중kg) + (4.799 × 키cm) - (5.677 × 나이)
```

**여성:**
```
BMR = 447.593 + (9.247 × 체중kg) + (3.098 × 키cm) - (4.330 × 나이)
```

### 2. TDEE (Total Daily Energy Expenditure)

```
TDEE = BMR × 활동계수
```

**활동 계수:**
- `sedentary` (좌식): 1.2
- `lightly_active` (주 1-3회): 1.375
- `moderately_active` (주 3-5회): 1.55
- `very_active` (주 6-7회): 1.725
- `extra_active` (하루 2회 이상): 1.9

### 3. 목표 칼로리

**목표별 조정:**
- 주 0.5kg 감량: TDEE - 500 kcal
- 주 1kg 감량: TDEE - 1000 kcal (최소 1200 kcal 보장)
- 체중 유지: TDEE
- 주 0.5kg 증량: TDEE + 300 kcal
- 주 1kg 증량: TDEE + 500 kcal

### 4. 영양소 비율

- 탄수화물: 40% (1g = 4 kcal)
- 단백질: 30% (1g = 4 kcal)
- 지방: 30% (1g = 9 kcal)

## API 엔드포인트

### 1. BMR 계산

```bash
POST /api/calculations/bmr
Authorization: Bearer {token}
Content-Type: application/json

{
  "gender": "male",  // male, female, 남성, 여성
  "weight": 70,      // kg (30-300)
  "height": 175,     // cm (100-250)
  "age": 30          // 세 (15-100)
}

Response:
{
  "success": true,
  "message": "BMR이 계산되었습니다.",
  "data": {
    "bmr": 1695.67,
    "unit": "kcal/day",
    "formula": "Harris-Benedict"
  }
}
```

### 2. TDEE 계산

```bash
POST /api/calculations/tdee
Authorization: Bearer {token}
Content-Type: application/json

{
  "bmr": 1695.67,
  "activity_level": "moderately_active"
}

Response:
{
  "success": true,
  "message": "TDEE가 계산되었습니다.",
  "data": {
    "tdee": 2628.29,
    "bmr": 1695.67,
    "activity_level": "moderately_active",
    "unit": "kcal/day"
  }
}
```

### 3. 목표 칼로리 계산

```bash
POST /api/calculations/target-calories
Authorization: Bearer {token}
Content-Type: application/json

{
  "tdee": 2628.29,
  "goal": "lose_0.5kg"
}

Response:
{
  "success": true,
  "message": "목표 칼로리가 계산되었습니다.",
  "data": {
    "target_calories": 2128.29,
    "tdee": 2628.29,
    "goal": "lose_0.5kg",
    "macros": {
      "protein_g": 159.62,
      "carbs_g": 212.83,
      "fat_g": 70.94
    },
    "unit": "kcal/day"
  }
}
```

### 4. 전체 계산 (설문 기반)

```bash
POST /api/calculations/calculate
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "칼로리 계산이 완료되었습니다.",
  "data": {
    "id": 1,
    "bmr": 1695.67,
    "tdee": 2628.29,
    "target_calories": 2128.29,
    "macros": {
      "protein_g": 159.62,
      "carbs_g": 212.83,
      "fat_g": 70.94
    },
    "calculated_at": "2025-12-03T15:00:00.000000Z"
  }
}
```

### 5. 최신 계산 조회

```bash
GET /api/calculations/latest
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "bmr": 1695.67,
    "tdee": 2628.29,
    "target_calories": 2128.29,
    "macros": {
      "protein_g": 159.62,
      "carbs_g": 212.83,
      "fat_g": 70.94
    },
    "calculated_at": "2025-12-03T15:00:00.000000Z"
  }
}
```

## 데이터베이스 스키마

### user_calculations 테이블

```sql
CREATE TABLE user_calculations (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  bmr DECIMAL(8,2) NOT NULL COMMENT 'Base Metabolic Rate',
  tdee DECIMAL(8,2) NOT NULL COMMENT 'Total Daily Energy Expenditure',
  target_calories DECIMAL(8,2) NOT NULL COMMENT 'Daily target calories',
  target_protein_g DECIMAL(8,2) NOT NULL COMMENT 'Daily target protein in grams',
  target_carbs_g DECIMAL(8,2) NOT NULL COMMENT 'Daily target carbs in grams',
  target_fat_g DECIMAL(8,2) NOT NULL COMMENT 'Daily target fat in grams',
  calculated_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id),
  INDEX (calculated_at)
);
```

## 테스트

```bash
# 칼로리 계산 테스트 실행
php artisan test --filter=CalorieCalculationTest

# 특정 테스트
php artisan test --filter=test_calculates_bmr_for_male_correctly
```

### 테스트 커버리지

- BMR 계산 (남성/여성)
- TDEE 계산 (활동 계수)
- 목표 칼로리 계산 (감량/유지/증량)
- 최소 칼로리 보장
- 영양소 비율 계산
- API 엔드포인트 검증
- 입력 검증

**총 10개 테스트**

## 사용 예시

```php
use App\Services\CalorieCalculationService;

$service = new CalorieCalculationService();

// 1. 개별 계산
$bmr = $service->calculateBMR('male', 70, 175, 30);
$tdee = $service->calculateTDEE($bmr, 'moderately_active');
$target = $service->calculateTargetCalories($tdee, 'lose_0.5kg');
$macros = $service->calculateMacros($target);

// 2. 설문 기반 자동 계산
$calculation = $service->calculateForUser($user);
echo $calculation->target_calories;

// 3. 최신 계산 조회
$latest = $service->getLatestCalculation($user);
```

## 참고사항

- 최소 칼로리는 안전을 위해 1200 kcal로 제한됩니다
- 설문 완료 후 자동으로 계산을 수행합니다
- 계산 결과는 데이터베이스에 저장되어 이력 관리가 가능합니다
- Harris-Benedict 공식은 가장 널리 사용되는 BMR 계산 공식입니다
