# Exercise Logging API Documentation

## Overview

The Exercise Logging API provides endpoints for users to track their daily physical activities. Users can log exercises manually, add exercises from the exercise database with automatic calorie calculation based on MET values, or quickly add exercises from their diet plan. The system tracks duration, calories burned, intensity, and provides daily summaries.

**Base URL:** `/api/daily-logs/exercises`

**Authentication:** All endpoints require authentication via Sanctum token.

---

## Endpoints

### 1. Get Exercise Logs for a Specific Date

Retrieve all exercises logged for a specific date.

**Endpoint:** `GET /api/daily-logs/exercises`

**Parameters:**
- `date` (required, string): Date in Y-m-d format (e.g., "2025-12-04")

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/daily-logs/exercises?date=2025-12-04" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise logs retrieved successfully",
  "data": {
    "date": "2025-12-04",
    "exercises": [
      {
        "id": 1,
        "user_id": 1,
        "date": "2025-12-04",
        "exercise_id": 5,
        "exercise_name": "조깅",
        "duration_minutes": 30,
        "calories_burned": "245.00",
        "intensity": "보통",
        "exercise_time": "07:00",
        "notes": "아침 운동",
        "created_at": "2025-12-04T07:00:00.000000Z",
        "updated_at": "2025-12-04T07:00:00.000000Z"
      },
      {
        "id": 2,
        "user_id": 1,
        "date": "2025-12-04",
        "exercise_id": 12,
        "exercise_name": "웨이트 트레이닝",
        "duration_minutes": 45,
        "calories_burned": "320.00",
        "intensity": "높음",
        "exercise_time": "18:30",
        "notes": null,
        "created_at": "2025-12-04T18:30:00.000000Z",
        "updated_at": "2025-12-04T18:30:00.000000Z"
      }
    ],
    "total_count": 2
  }
}
```

---

### 2. Log an Exercise

Log a new exercise entry. Supports both manual entry and automatic calorie calculation from exercise database.

**Endpoint:** `POST /api/daily-logs/exercises`

**Request Body:**

**Option A: Manual Entry (calories required)**
```json
{
  "date": "2025-12-04",
  "exercise_name": "조깅",
  "duration_minutes": 30,
  "calories_burned": 245,
  "intensity": "보통",
  "exercise_time": "07:00",
  "notes": "아침 운동"
}
```

**Option B: From Exercise Database (calories auto-calculated)**
```json
{
  "date": "2025-12-04",
  "exercise_id": 5,
  "duration_minutes": 30
}
```

**Validation Rules:**
- `date`: required, valid date
- `exercise_id`: optional, must exist in exercises table
- `exercise_name`: required if exercise_id is not provided, max 255 characters
- `duration_minutes`: required, integer, min: 1, max: 999
- `calories_burned`: required if exercise_id is not provided, numeric, min: 0, max: 9999.99
- `intensity`: optional, one of: 낮음, 보통, 높음, 매우 높음
- `exercise_time`: optional, format: H:i (e.g., "07:00")
- `notes`: optional, string, max 1000 characters

**Example Request (Manual):**
```bash
curl -X POST "https://api.myfit.com/api/daily-logs/exercises" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2025-12-04",
    "exercise_name": "조깅",
    "duration_minutes": 30,
    "calories_burned": 245,
    "intensity": "보통",
    "exercise_time": "07:00",
    "notes": "아침 운동"
  }'
```

**Example Request (From Exercise DB):**
```bash
curl -X POST "https://api.myfit.com/api/daily-logs/exercises" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2025-12-04",
    "exercise_id": 5,
    "duration_minutes": 30
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise logged successfully",
  "data": {
    "id": 3,
    "user_id": 1,
    "date": "2025-12-04",
    "exercise_id": 5,
    "exercise_name": "조깅",
    "duration_minutes": 30,
    "calories_burned": "245.00",
    "intensity": "보통",
    "exercise_time": null,
    "notes": null,
    "created_at": "2025-12-04T08:00:00.000000Z",
    "updated_at": "2025-12-04T08:00:00.000000Z"
  }
}
```

**Calorie Calculation Formula:**
When using `exercise_id`, calories are automatically calculated using:
```
Calories = MET Value × User Weight (kg) × Duration (hours)
```

**Example:**
- Exercise: 조깅 (MET: 7.0)
- User Weight: 70 kg
- Duration: 30 minutes (0.5 hours)
- Calories = 7.0 × 70 × 0.5 = 245 calories

---

### 3. Update an Exercise Log

Update an existing exercise log entry.

**Endpoint:** `PUT /api/daily-logs/exercises/{id}`

**Path Parameters:**
- `id` (required): Exercise log ID

**Request Body:**
```json
{
  "duration_minutes": 45,
  "calories_burned": 300,
  "notes": "Updated note"
}
```

**Validation Rules:** Same as create, but all fields are optional (use `sometimes` validation)

**Example Request:**
```bash
curl -X PUT "https://api.myfit.com/api/daily-logs/exercises/3" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "duration_minutes": 45,
    "calories_burned": 300,
    "notes": "운동 시간 연장"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise log updated successfully",
  "data": {
    "id": 3,
    "user_id": 1,
    "date": "2025-12-04",
    "exercise_id": 5,
    "exercise_name": "조깅",
    "duration_minutes": 45,
    "calories_burned": "300.00",
    "intensity": "보통",
    "exercise_time": null,
    "notes": "운동 시간 연장",
    "created_at": "2025-12-04T08:00:00.000000Z",
    "updated_at": "2025-12-04T14:30:00.000000Z"
  }
}
```

**Authorization:** Users can only update their own exercise logs.

**Error Responses:**
- `404 Not Found`: Exercise log not found
- `403 Forbidden`: User does not have access to this exercise log

---

### 4. Delete an Exercise Log

Delete an exercise log entry.

**Endpoint:** `DELETE /api/daily-logs/exercises/{id}`

**Path Parameters:**
- `id` (required): Exercise log ID

**Example Request:**
```bash
curl -X DELETE "https://api.myfit.com/api/daily-logs/exercises/3" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise log deleted successfully",
  "data": null
}
```

**Authorization:** Users can only delete their own exercise logs.

**Error Responses:**
- `404 Not Found`: Exercise log not found
- `403 Forbidden`: User does not have access to this exercise log

---

### 5. Get Daily Summary

Get a summary of all exercises logged for a specific date, including totals by intensity.

**Endpoint:** `GET /api/daily-logs/exercises/summary`

**Parameters:**
- `date` (required, string): Date in Y-m-d format

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/daily-logs/exercises/summary?date=2025-12-04" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Daily exercise summary retrieved successfully",
  "data": {
    "date": "2025-12-04",
    "total_duration_minutes": 120,
    "total_calories_burned": 650,
    "exercise_count": 3,
    "exercises_by_intensity": {
      "낮음": 0,
      "보통": 1,
      "높음": 2,
      "매우 높음": 0
    }
  }
}
```

**Notes:**
- `total_duration_minutes`: Sum of all exercise durations
- `total_calories_burned`: Sum of all calories burned
- `exercise_count`: Total number of exercises logged
- `exercises_by_intensity`: Count of exercises by intensity level

---

### 6. Log Exercise from Diet Plan

Quickly add an exercise from the user's diet plan to their daily log.

**Endpoint:** `POST /api/daily-logs/exercises/from-plan`

**Request Body:**
```json
{
  "daily_exercise_plan_id": 25,
  "date": "2025-12-04",
  "exercise_time": "07:00"
}
```

**Validation Rules:**
- `daily_exercise_plan_id`: required, must exist in daily_exercise_plans table
- `date`: required, valid date
- `exercise_time`: optional, format: H:i

**Example Request:**
```bash
curl -X POST "https://api.myfit.com/api/daily-logs/exercises/from-plan" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "daily_exercise_plan_id": 25,
    "date": "2025-12-04",
    "exercise_time": "07:00"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise logged from plan successfully",
  "data": {
    "id": 10,
    "user_id": 1,
    "date": "2025-12-04",
    "exercise_id": 8,
    "exercise_name": "조깅",
    "duration_minutes": 30,
    "calories_burned": "245.00",
    "intensity": "보통",
    "exercise_time": "07:00",
    "notes": "플랜에서 추가",
    "created_at": "2025-12-04T07:00:00.000000Z",
    "updated_at": "2025-12-04T07:00:00.000000Z"
  }
}
```

**Authorization:** Users can only log exercises from their own diet plans.

**Error Responses:**
- `403 Forbidden`: User does not have access to this exercise plan
- `422 Validation Error`: Invalid daily_exercise_plan_id

**Notes:**
- All data is copied from the daily exercise plan
- Automatically adds "플랜에서 추가" (Added from plan) to notes
- Useful for quickly following the diet plan

---

## Data Models

### ExerciseLog Model

```php
{
  "id": integer,
  "user_id": integer,
  "date": date (Y-m-d),
  "exercise_id": integer|null,
  "exercise_name": string,
  "duration_minutes": integer,
  "calories_burned": decimal(8,2),
  "intensity": enum (낮음|보통|높음|매우 높음)|null,
  "exercise_time": time (H:i)|null,
  "notes": text|null,
  "created_at": timestamp,
  "updated_at": timestamp
}
```

### Relationships

- **User**: Each exercise log belongs to a user
- **Exercise**: Optional reference to exercise database (can be null for manual entries)

---

## Usage Examples

### Typical Daily Workflow

#### 1. Log morning cardio from exercise database
```bash
POST /api/daily-logs/exercises
{
  "date": "2025-12-04",
  "exercise_id": 5,
  "duration_minutes": 30,
  "exercise_time": "07:00"
}
```

#### 2. Log evening workout from diet plan
```bash
POST /api/daily-logs/exercises/from-plan
{
  "daily_exercise_plan_id": 25,
  "date": "2025-12-04",
  "exercise_time": "18:30"
}
```

#### 3. Log additional activity manually
```bash
POST /api/daily-logs/exercises
{
  "date": "2025-12-04",
  "exercise_name": "계단 오르기",
  "duration_minutes": 15,
  "calories_burned": 100,
  "intensity": "보통",
  "exercise_time": "12:00"
}
```

#### 4. Check daily progress
```bash
GET /api/daily-logs/exercises/summary?date=2025-12-04
```

#### 5. View all exercises for the day
```bash
GET /api/daily-logs/exercises?date=2025-12-04
```

---

## Intensity Levels

The system supports four intensity levels:

| Korean | English | Description | Typical MET Range |
|--------|---------|-------------|-------------------|
| 낮음 | Low | Light activities, minimal effort | 2.0 - 3.5 |
| 보통 | Medium | Moderate activities, noticeable effort | 3.5 - 6.0 |
| 높음 | High | Vigorous activities, hard effort | 6.0 - 9.0 |
| 매우 높음 | Very High | Very vigorous activities, maximum effort | 9.0+ |

---

## MET (Metabolic Equivalent of Task) Values

MET values represent the energy cost of physical activities. Common exercises and their MET values:

| Exercise | Korean | MET Value | Intensity |
|----------|--------|-----------|-----------|
| Walking (slow) | 걷기 (천천히) | 2.5 | 낮음 |
| Walking (brisk) | 걷기 (빠르게) | 4.0 | 보통 |
| Jogging | 조깅 | 7.0 | 보통 |
| Running | 달리기 | 11.0 | 매우 높음 |
| Cycling (leisure) | 자전거 (여유) | 4.0 | 보통 |
| Cycling (moderate) | 자전거 (보통) | 6.8 | 보통 |
| Swimming (moderate) | 수영 (보통) | 8.0 | 높음 |
| Weight Training | 웨이트 트레이닝 | 6.0 | 높음 |
| Yoga | 요가 | 2.5 | 낮음 |
| Pilates | 필라테스 | 3.0 | 낮음 |

---

## Error Handling

### Common Error Responses

**401 Unauthorized**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden**
```json
{
  "success": false,
  "message": "You do not have access to this exercise log",
  "data": null
}
```

**404 Not Found**
```json
{
  "success": false,
  "message": "Exercise log not found",
  "data": null
}
```

**422 Validation Error**
```json
{
  "success": false,
  "message": "Validation failed",
  "data": {
    "date": ["The date field is required."],
    "duration_minutes": ["The duration minutes field is required."]
  }
}
```

---

## Business Rules

1. **Automatic Calorie Calculation**: When logging from exercise database, calories are calculated using MET formula
2. **User Weight**: Calorie calculation uses user's current weight (defaults to 70kg if not set)
3. **Authorization**: Users can only access their own exercise logs
4. **Intensity Levels**: Four intensity levels supported (낮음, 보통, 높음, 매우 높음)
5. **Date Format**: All dates must be in Y-m-d format (e.g., "2025-12-04")
6. **Time Format**: Exercise time uses H:i format (e.g., "07:00")
7. **Decimal Precision**: Calories burned are stored and returned with 2 decimal places
8. **Duration**: Measured in minutes (integer)

---

## Database Schema

```sql
CREATE TABLE exercise_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    date DATE NOT NULL,
    exercise_id BIGINT NULL,
    exercise_name VARCHAR(255) NOT NULL,
    duration_minutes INT NOT NULL,
    calories_burned DECIMAL(8,2) NOT NULL,
    intensity ENUM('낮음', '보통', '높음', '매우 높음') NULL,
    exercise_time TIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE SET NULL,

    INDEX idx_user_id (user_id),
    INDEX idx_date (date),
    INDEX idx_user_date (user_id, date),
    INDEX idx_intensity (intensity)
);
```

---

## Performance Considerations

1. **Indexes**: Database indexes on user_id, date, and [user_id, date] for fast queries
2. **Date Scoping**: Always query by specific date to limit result set
3. **Eager Loading**: Exercise relationship can be eager loaded to prevent N+1 queries
4. **Calorie Calculation**: Done in application layer, no database triggers

---

## Calorie Calculation Details

### Formula
```
Calories Burned = MET Value × User Weight (kg) × Duration (hours)
```

### Example Calculations

**Example 1: Jogging**
- MET Value: 7.0
- User Weight: 70 kg
- Duration: 30 minutes = 0.5 hours
- Calories = 7.0 × 70 × 0.5 = **245 calories**

**Example 2: Swimming**
- MET Value: 8.0
- User Weight: 65 kg
- Duration: 45 minutes = 0.75 hours
- Calories = 8.0 × 65 × 0.75 = **390 calories**

**Example 3: Weight Training**
- MET Value: 6.0
- User Weight: 80 kg
- Duration: 60 minutes = 1.0 hours
- Calories = 6.0 × 80 × 1.0 = **480 calories**

---

## Integration with Other Features

### Diet Plan Integration
- Exercises can be added from the diet plan's daily exercise schedule
- Seamless tracking of planned vs. actual exercises

### Daily Dashboard Integration
- Exercise logs feed into the daily dashboard
- Combined with meal logs for complete activity tracking

### Progress Tracking
- Weekly/monthly summaries can aggregate exercise data
- Track consistency and improvement over time

---

## Future Enhancements

- Heart rate monitoring integration
- GPS tracking for outdoor activities
- Exercise video demonstrations
- Social challenges and competitions
- Achievement badges
- Integration with fitness wearables
- Custom exercise creation
- Exercise routines/templates
- Rest period tracking between sets
- Personal records tracking
