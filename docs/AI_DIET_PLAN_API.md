# AI Diet Plan Generation API Documentation

## Overview

The AI Diet Plan Generation system creates personalized 7-day diet and exercise plans using GPT-4 AI technology. Plans are generated based on user survey data, health metrics, and preferences.

## Table of Contents

- [Authentication](#authentication)
- [Plan Generation Flow](#plan-generation-flow)
- [API Endpoints](#api-endpoints)
- [Data Models](#data-models)
- [GPT Integration](#gpt-integration)
- [Error Handling](#error-handling)

---

## Authentication

All endpoints require authentication using Laravel Sanctum bearer tokens.

**Header:**
```
Authorization: Bearer {your-token}
```

---

## Plan Generation Flow

```
1. User completes survey → 2. Request generation → 3. Job queued
         ↓                         ↓                      ↓
4. GPT generates plan → 5. Plan saved → 6. Status: active
```

**Typical Timeline:**
- Initial request: < 1 second (returns 202)
- GPT processing: 30-60 seconds
- Total generation: 1-2 minutes

---

## API Endpoints

### 1. Generate Diet Plan

Create a new personalized diet plan (async).

**Endpoint:** `POST /api/diet-plans/generate`

**Authentication:** Required

**Request Body:**
```json
{
  "survey_response_id": 123  // Optional: specific survey response
}
```

**Request Parameters:**
- `survey_response_id` (integer, optional): ID of specific survey response to use. If omitted, uses latest survey.

**Success Response (202 Accepted):**
```json
{
  "success": true,
  "message": "Diet plan generation started. Check status using the provided ID.",
  "data": {
    "diet_plan_id": 456,
    "status": "generating"
  }
}
```

**Error Response (409 Conflict):**
```json
{
  "success": false,
  "message": "You already have an active diet plan. Please complete or archive it before generating a new one.",
  "errors": null
}
```

**Example Request:**
```bash
curl -X POST "https://api.myfit.com/api/diet-plans/generate" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "survey_response_id": 123
  }'
```

**Notes:**
- Generation is asynchronous (background job)
- Only one active plan allowed per user
- Uses GPT-4 for AI generation

---

### 2. Check Generation Status

Check the current status of a diet plan generation.

**Endpoint:** `GET /api/diet-plans/generation-status/{id}`

**Authentication:** Required

**Path Parameters:**
- `id` (integer): Diet plan ID

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Generation status retrieved",
  "data": {
    "diet_plan_id": 456,
    "status": "generating",
    "is_generating": true,
    "is_active": false,
    "created_at": "2025-12-03T10:00:00.000000Z"
  }
}
```

**Status Values:**
- `generating`: Plan is currently being created
- `active`: Plan is ready and active
- `completed`: Plan period has ended
- `archived`: Plan has been archived

**Error Responses:**

404 Not Found:
```json
{
  "success": false,
  "message": "Diet plan not found"
}
```

403 Forbidden:
```json
{
  "success": false,
  "message": "You do not have access to this diet plan"
}
```

**Polling Recommendation:**
- Poll every 5-10 seconds while `status === 'generating'`
- Stop polling when `status === 'active'`
- Maximum generation time: ~2 minutes

---

### 3. Get Active Diet Plan

Retrieve the user's currently active diet plan with all details.

**Endpoint:** `GET /api/diet-plans/active`

**Authentication:** Required

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Active diet plan retrieved",
  "data": {
    "id": 456,
    "status": "active",
    "start_date": "2025-12-03",
    "end_date": "2025-12-09",
    "target_calories_per_day": 1500,
    "ai_summary": "This plan focuses on balanced nutrition with moderate exercise...",
    "daily_meal_plans": [
      {
        "day_number": 1,
        "date": "2025-12-03",
        "total_calories": 1480,
        "total_protein_g": 112,
        "total_carbs_g": 150,
        "total_fat_g": 50,
        "tips": "Stay hydrated and eat slowly",
        "meals": {
          "breakfast": [
            {
              "id": 1001,
              "food_name": "백미밥",
              "serving_size": 210,
              "calories": 300,
              "protein_g": 5.4,
              "carbs_g": 65.8,
              "fat_g": 0.6,
              "notes": null
            },
            {
              "id": 1002,
              "food_name": "계란후라이",
              "serving_size": 60,
              "calories": 90,
              "protein_g": 6,
              "carbs_g": 0.6,
              "fat_g": 7,
              "notes": null
            }
          ],
          "lunch": [...],
          "dinner": [...],
          "snack": [...]
        }
      }
      // ... days 2-7
    ],
    "daily_exercise_plans": [
      {
        "day_number": 1,
        "exercises": [
          {
            "id": 2001,
            "exercise_name": "빠르게 걷기",
            "duration_minutes": 30,
            "estimated_calories_burned": 150,
            "intensity": "보통",
            "notes": "편한 속도로 시작하세요"
          }
        ]
      }
      // ... other days with exercises
    ]
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "message": "No active diet plan found",
  "errors": null
}
```

**Notes:**
- Returns complete 7-day plan with all meals and exercises
- Includes nutritional breakdown for each day
- Exercise plans only exist on workout days (3-5 days per week)

---

### 4. Get Specific Diet Plan

Retrieve details of a specific diet plan by ID.

**Endpoint:** `GET /api/diet-plans/{id}`

**Authentication:** Required

**Path Parameters:**
- `id` (integer): Diet plan ID

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Diet plan retrieved",
  "data": {
    "id": 456,
    "status": "active",
    "start_date": "2025-12-03",
    "end_date": "2025-12-09",
    "target_calories_per_day": 1500,
    "ai_summary": "This plan focuses on balanced nutrition...",
    "daily_meal_plans": [...],
    "daily_exercise_plans": [...]
  }
}
```

**Error Responses:**

404 Not Found:
```json
{
  "success": false,
  "message": "Diet plan not found"
}
```

403 Forbidden:
```json
{
  "success": false,
  "message": "You do not have access to this diet plan"
}
```

---

### 5. Get Plan for Specific Day

Retrieve meal and exercise plan for a specific day.

**Endpoint:** `GET /api/diet-plans/{id}/day/{day}`

**Authentication:** Required

**Path Parameters:**
- `id` (integer): Diet plan ID
- `day` (integer): Day number (1-7)

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Day plan retrieved",
  "data": {
    "day_number": 1,
    "date": "2025-12-03",
    "meals": {
      "breakfast": [
        {
          "id": 1001,
          "food_name": "백미밥",
          "serving_size": 210,
          "calories": 300,
          "protein_g": 5.4,
          "carbs_g": 65.8,
          "fat_g": 0.6,
          "notes": null
        }
      ],
      "lunch": [...],
      "dinner": [...],
      "snack": [...]
    },
    "total_calories": 1480,
    "total_protein_g": 112,
    "total_carbs_g": 150,
    "total_fat_g": 50,
    "tips": "Stay hydrated and eat slowly",
    "exercises": [
      {
        "id": 2001,
        "exercise_name": "빠르게 걷기",
        "duration_minutes": 30,
        "estimated_calories_burned": 150,
        "intensity": "보통",
        "notes": "편한 속도로 시작하세요"
      }
    ]
  }
}
```

**Error Responses:**

400 Bad Request:
```json
{
  "success": false,
  "message": "Invalid day number. Must be between 1 and 7."
}
```

404 Not Found:
```json
{
  "success": false,
  "message": "No meal plan found for this day"
}
```

**Notes:**
- Day numbers are 1-indexed (1-7)
- Exercise array may be empty on rest days

---

### 6. Regenerate Diet Plan

Create a new plan and archive the old one.

**Endpoint:** `POST /api/diet-plans/{id}/regenerate`

**Authentication:** Required

**Path Parameters:**
- `id` (integer): Current diet plan ID to regenerate

**Success Response (202 Accepted):**
```json
{
  "success": true,
  "message": "Diet plan regeneration started",
  "data": {
    "old_plan_id": 456,
    "new_plan_id": 789,
    "status": "generating"
  }
}
```

**Error Responses:**

404 Not Found:
```json
{
  "success": false,
  "message": "Diet plan not found"
}
```

403 Forbidden:
```json
{
  "success": false,
  "message": "You do not have access to this diet plan"
}
```

**Notes:**
- Old plan is automatically archived
- New plan uses same survey data as original
- Generation is asynchronous

---

### 7. Replace Meal Item

Replace a meal item with a different food (manual or automatic).

**Endpoint:** `PUT /api/diet-plans/meals/{mealItemId}/replace`

**Authentication:** Required

**Path Parameters:**
- `mealItemId` (integer): Meal plan item ID to replace

**Request Body:**
```json
{
  "replacement_food_id": 123  // Optional: specific food to use
}
```

**Request Parameters:**
- `replacement_food_id` (integer, optional): ID of specific food to use as replacement. If omitted, system automatically finds similar food.

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Meal item replaced successfully",
  "data": {
    "meal_item": {
      "id": 1001,
      "food_name": "현미밥",
      "serving_size": 210,
      "calories": 300,
      "protein_g": 6,
      "carbs_g": 60,
      "fat_g": 1.5,
      "notes": null
    },
    "daily_totals": {
      "total_calories": 1490,
      "total_protein_g": 113,
      "total_carbs_g": 145,
      "total_fat_g": 51
    }
  }
}
```

**Replacement Logic:**
- **Manual**: If `replacement_food_id` provided, uses that specific food
- **Automatic**: Finds food with:
  - Same category (if available)
  - Similar calories (±20%)
  - Random selection from matches
- Serving size automatically adjusted to match calorie target
- Daily totals automatically recalculated

**Error Responses:**

404 Not Found:
```json
{
  "success": false,
  "message": "Meal item not found"
}
```

403 Forbidden:
```json
{
  "success": false,
  "message": "You do not have access to this meal item"
}
```

400 Bad Request:
```json
{
  "success": false,
  "message": "No suitable replacement food found"
}
```

---

### 8. Get Meal Replacement Suggestions

Get a list of suggested foods for replacement.

**Endpoint:** `GET /api/diet-plans/meals/{mealItemId}/suggestions`

**Authentication:** Required

**Path Parameters:**
- `mealItemId` (integer): Meal plan item ID

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Replacement suggestions retrieved",
  "data": [
    {
      "id": 5,
      "name": "현미밥",
      "name_en": "Brown Rice",
      "category": "곡류",
      "serving_size": 210,
      "calories": 280,
      "protein_g": 6,
      "carbs_g": 60,
      "fat_g": 1.5
    },
    {
      "id": 12,
      "name": "잡곡밥",
      "name_en": "Mixed Grain Rice",
      "category": "곡류",
      "serving_size": 210,
      "calories": 290,
      "protein_g": 6.5,
      "carbs_g": 62,
      "fat_g": 1.8
    }
    // ... up to 5 suggestions
  ]
}
```

**Notes:**
- Returns up to 5 similar foods
- Same category preference
- Calories within ±20% of original
- Excludes current food

---

### 9. Replace Exercise

Replace an exercise with a different one (manual or automatic).

**Endpoint:** `PUT /api/diet-plans/exercises/{exerciseId}/replace`

**Authentication:** Required

**Path Parameters:**
- `exerciseId` (integer): Daily exercise plan ID to replace

**Request Body:**
```json
{
  "replacement_exercise_id": 45  // Optional: specific exercise to use
}
```

**Request Parameters:**
- `replacement_exercise_id` (integer, optional): ID of specific exercise. If omitted, system finds similar exercise.

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Exercise replaced successfully",
  "data": {
    "id": 2001,
    "exercise_name": "조깅",
    "duration_minutes": 30,
    "estimated_calories_burned": 245,
    "intensity": "보통"
  }
}
```

**Replacement Logic:**
- **Manual**: If `replacement_exercise_id` provided, uses that exercise
- **Automatic**: Finds exercise with:
  - Same intensity level
  - Same category (if available)
  - Random selection from matches
- Duration maintained
- Calories recalculated based on new exercise

**Error Responses:**

404 Not Found:
```json
{
  "success": false,
  "message": "Exercise plan not found"
}
```

403 Forbidden:
```json
{
  "success": false,
  "message": "You do not have access to this exercise plan"
}
```

400 Bad Request:
```json
{
  "success": false,
  "message": "No suitable replacement exercise found"
}
```

---

### 10. Get Exercise Replacement Suggestions

Get a list of suggested exercises for replacement.

**Endpoint:** `GET /api/diet-plans/exercises/{exerciseId}/suggestions`

**Authentication:** Required

**Path Parameters:**
- `exerciseId` (integer): Daily exercise plan ID

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Replacement suggestions retrieved",
  "data": [
    {
      "id": 3,
      "name": "조깅",
      "category": "유산소",
      "intensity": "보통",
      "met_value": 7.0,
      "calories_per_hour_per_kg": 7.0,
      "description": "천천히 달리기"
    },
    {
      "id": 8,
      "name": "사이클링",
      "category": "유산소",
      "intensity": "보통",
      "met_value": 6.8,
      "calories_per_hour_per_kg": 6.8,
      "description": "자전거 타기"
    }
    // ... up to 5 suggestions
  ]
}
```

**Notes:**
- Returns up to 5 similar exercises
- Same intensity level
- Same category preference
- Excludes current exercise

---

## Data Models

### Diet Plan

```typescript
{
  id: number
  user_id: number
  survey_response_id: number | null
  status: 'generating' | 'active' | 'completed' | 'archived'
  start_date: string  // YYYY-MM-DD
  end_date: string    // YYYY-MM-DD
  target_calories_per_day: number
  ai_summary: string | null
  generation_prompt: string | null
  created_at: string
  updated_at: string
}
```

### Daily Meal Plan

```typescript
{
  id: number
  diet_plan_id: number
  day_number: number  // 1-7
  date: string       // YYYY-MM-DD
  total_calories: number
  total_protein_g: number
  total_carbs_g: number
  total_fat_g: number
  tips: string | null
  created_at: string
  updated_at: string
}
```

### Meal Plan Item

```typescript
{
  id: number
  daily_meal_plan_id: number
  meal_type: 'breakfast' | 'lunch' | 'dinner' | 'snack'
  food_id: number | null
  food_name: string
  serving_size: number  // grams
  calories: number
  protein_g: number
  carbs_g: number
  fat_g: number
  order: number
  notes: string | null
  created_at: string
  updated_at: string
}
```

### Daily Exercise Plan

```typescript
{
  id: number
  diet_plan_id: number
  day_number: number  // 1-7
  date: string       // YYYY-MM-DD
  exercise_id: number | null
  exercise_name: string
  duration_minutes: number
  estimated_calories_burned: number
  intensity: '낮음' | '보통' | '높음'
  notes: string | null
  created_at: string
  updated_at: string
}
```

---

## GPT Integration

### How It Works

1. **Data Collection**: System gathers user survey data, health metrics, and preferences
2. **Prompt Generation**: Creates detailed prompt with user context
3. **GPT-4 Call**: Sends request to OpenAI API with JSON response format
4. **Response Parsing**: Validates and parses JSON response
5. **Database Storage**: Saves all meals and exercises to database

### GPT Prompt Structure

```
당신은 영양학과 운동 전문가입니다.

[사용자 정보]
- 성별, 나이, 체중, 키
- 목표 및 기간
- 활동량 및 운동 경험
- 식습관 및 선호도

[영양소 목표]
- 일일 칼로리
- 탄단지 비율

[플랜 요구사항]
- 7일 식단 (아침/점심/저녁/간식)
- 영양소 정보
- 주 3-5회 운동
- 실천 팁

[응답 형식: JSON]
...
```

### Response Validation

The system validates:
- ✅ Valid JSON format
- ✅ All 7 days present
- ✅ Nutritional data accuracy
- ✅ Calorie targets within ±10%
- ✅ Required meal types present

### Error Handling

**Retry Logic:**
- Maximum 3 attempts
- 60-second backoff between retries
- Logs all failures

**Common Issues:**
- API timeout: Retried automatically
- Invalid JSON: Logged and retried
- Missing data: Validation error

---

## Configuration

### Environment Variables

Required in `.env`:

```env
OPENAI_API_KEY=sk-...your-api-key...
```

### Config File

Add to `config/services.php`:

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
],
```

---

## Error Handling

### HTTP Status Codes

- `200 OK`: Request successful
- `202 Accepted`: Async operation started
- `400 Bad Request`: Invalid parameters
- `401 Unauthorized`: Missing/invalid token
- `403 Forbidden`: Access denied
- `404 Not Found`: Resource not found
- `409 Conflict`: Conflicting state (e.g., active plan exists)
- `422 Unprocessable Entity`: Validation failed
- `500 Internal Server Error`: Server error

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error detail"]
  }
}
```

---

## Usage Examples

### Complete Flow Example

```javascript
// 1. Request plan generation
const generateResponse = await fetch('/api/diet-plans/generate', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});

const { data } = await generateResponse.json();
const planId = data.diet_plan_id;

// 2. Poll for completion
const checkStatus = async () => {
  const response = await fetch(`/api/diet-plans/generation-status/${planId}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });

  const { data } = await response.json();

  if (data.is_active) {
    // Plan is ready!
    return true;
  } else if (data.is_generating) {
    // Still generating, wait and check again
    await new Promise(resolve => setTimeout(resolve, 5000));
    return checkStatus();
  }
};

await checkStatus();

// 3. Get active plan
const planResponse = await fetch('/api/diet-plans/active', {
  headers: { 'Authorization': `Bearer ${token}` }
});

const plan = await planResponse.json();
console.log(plan.data);
```

### Get Today's Meal Plan

```javascript
const today = new Date();
const dayOfWeek = today.getDay(); // 0-6 (Sunday-Saturday)
const planDay = dayOfWeek === 0 ? 7 : dayOfWeek; // Convert to 1-7

const response = await fetch(`/api/diet-plans/${planId}/day/${planDay}`, {
  headers: { 'Authorization': `Bearer ${token}` }
});

const todaysPlan = await response.json();
```

---

## Best Practices

1. **Polling**: Use exponential backoff when checking generation status
2. **Caching**: Cache active plan data to reduce API calls
3. **Error Handling**: Always handle 409 (active plan exists) gracefully
4. **Loading States**: Show clear UI feedback during generation
5. **Timeouts**: Set reasonable timeout (2-3 minutes) for generation

---

## Limitations

- **One Active Plan**: Users can only have one active plan at a time
- **Generation Time**: 1-2 minutes average (GPT-4 dependent)
- **Plan Duration**: Fixed 7-day plans
- **API Costs**: GPT-4 API calls incur costs (~$0.10-0.20 per plan)

---

## Future Enhancements

Planned features for Sub-Epic 1.3.4:

- [ ] Meal replacement suggestions
- [ ] Exercise swapping
- [ ] Plan customization
- [ ] Multi-week plans
- [ ] Shopping list generation
- [ ] Recipe details

---

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Monitor queue: `php artisan queue:work`
- Test GPT: Ensure API key is valid

---

## Changelog

### v1.0.0 (2025-12-03)
- Initial release
- GPT-4 integration
- 7-day plan generation
- Async processing
- Complete CRUD operations
