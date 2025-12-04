# Meal Logging API Documentation

## Overview

The Meal Logging API provides endpoints for users to track their daily food intake. Users can log meals manually, add meals from the food database with automatic nutrient calculation, or quickly add meals from their diet plan. The system tracks calories, macronutrients (protein, carbs, fat), and provides daily summaries with comparisons to target calories.

**Base URL:** `/api/daily-logs/meals`

**Authentication:** All endpoints require authentication via Sanctum token.

---

## Endpoints

### 1. Get Meal Logs for a Specific Date

Retrieve all meals logged for a specific date, grouped by meal type.

**Endpoint:** `GET /api/daily-logs/meals`

**Parameters:**
- `date` (required, string): Date in Y-m-d format (e.g., "2025-12-04")

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/daily-logs/meals?date=2025-12-04" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Meal logs retrieved successfully",
  "data": {
    "date": "2025-12-04",
    "meals": {
      "breakfast": [
        {
          "id": 1,
          "user_id": 1,
          "date": "2025-12-04",
          "meal_type": "breakfast",
          "food_id": 15,
          "food_name": "계란 프라이",
          "serving_size": "100.00",
          "calories": "150.00",
          "protein_g": "12.00",
          "carbs_g": "2.00",
          "fat_g": "10.00",
          "meal_time": "08:30",
          "notes": "아침 식사",
          "created_at": "2025-12-04T08:30:00.000000Z",
          "updated_at": "2025-12-04T08:30:00.000000Z"
        }
      ],
      "lunch": [
        {
          "id": 2,
          "user_id": 1,
          "date": "2025-12-04",
          "meal_type": "lunch",
          "food_id": 23,
          "food_name": "현미밥",
          "serving_size": "210.00",
          "calories": "300.00",
          "protein_g": "6.00",
          "carbs_g": "65.00",
          "fat_g": "2.00",
          "meal_time": "12:30",
          "notes": null,
          "created_at": "2025-12-04T12:30:00.000000Z",
          "updated_at": "2025-12-04T12:30:00.000000Z"
        }
      ],
      "dinner": [],
      "snack": []
    },
    "total_count": 2
  }
}
```

---

### 2. Log a Meal

Log a new meal entry. Supports both manual entry and automatic calculation from food database.

**Endpoint:** `POST /api/daily-logs/meals`

**Request Body:**

**Option A: Manual Entry (all nutrient data required)**
```json
{
  "date": "2025-12-04",
  "meal_type": "breakfast",
  "food_name": "계란 프라이",
  "serving_size": 100,
  "calories": 150,
  "protein_g": 12,
  "carbs_g": 2,
  "fat_g": 10,
  "meal_time": "08:30",
  "notes": "아침 식사"
}
```

**Option B: From Food Database (nutrients auto-calculated)**
```json
{
  "date": "2025-12-04",
  "meal_type": "lunch",
  "food_id": 23,
  "serving_size": 105
}
```

**Validation Rules:**
- `date`: required, valid date
- `meal_type`: required, one of: breakfast, lunch, dinner, snack
- `food_id`: optional, must exist in foods table
- `food_name`: required if food_id is not provided, max 255 characters
- `serving_size`: required, numeric, min: 0.01, max: 9999.99
- `calories`: required if food_id is not provided, numeric, min: 0, max: 9999.99
- `protein_g`: required if food_id is not provided, numeric, min: 0, max: 999.99
- `carbs_g`: required if food_id is not provided, numeric, min: 0, max: 999.99
- `fat_g`: required if food_id is not provided, numeric, min: 0, max: 999.99
- `meal_time`: optional, format: H:i (e.g., "08:30")
- `notes`: optional, string, max 1000 characters

**Example Request (Manual):**
```bash
curl -X POST "https://api.myfit.com/api/daily-logs/meals" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2025-12-04",
    "meal_type": "breakfast",
    "food_name": "계란 프라이",
    "serving_size": 100,
    "calories": 150,
    "protein_g": 12,
    "carbs_g": 2,
    "fat_g": 10,
    "meal_time": "08:30",
    "notes": "아침 식사"
  }'
```

**Example Request (From Food DB):**
```bash
curl -X POST "https://api.myfit.com/api/daily-logs/meals" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2025-12-04",
    "meal_type": "lunch",
    "food_id": 23,
    "serving_size": 105
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Meal logged successfully",
  "data": {
    "id": 3,
    "user_id": 1,
    "date": "2025-12-04",
    "meal_type": "lunch",
    "food_id": 23,
    "food_name": "현미밥",
    "serving_size": "105.00",
    "calories": "150.00",
    "protein_g": "3.00",
    "carbs_g": "32.50",
    "fat_g": "1.00",
    "meal_time": null,
    "notes": null,
    "created_at": "2025-12-04T12:00:00.000000Z",
    "updated_at": "2025-12-04T12:00:00.000000Z"
  }
}
```

**Note:** When using `food_id`, the system automatically:
1. Retrieves food data from the database
2. Calculates serving ratio (requested serving / food's base serving)
3. Applies ratio to all nutrients
4. Rounds to 2 decimal places

---

### 3. Update a Meal Log

Update an existing meal log entry.

**Endpoint:** `PUT /api/daily-logs/meals/{id}`

**Path Parameters:**
- `id` (required): Meal log ID

**Request Body:**
```json
{
  "calories": 250,
  "protein_g": 15,
  "notes": "Updated note"
}
```

**Validation Rules:** Same as create, but all fields are optional (use `sometimes` validation)

**Example Request:**
```bash
curl -X PUT "https://api.myfit.com/api/daily-logs/meals/3" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "calories": 250,
    "protein_g": 15,
    "notes": "점심 추가 섭취"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Meal log updated successfully",
  "data": {
    "id": 3,
    "user_id": 1,
    "date": "2025-12-04",
    "meal_type": "lunch",
    "food_id": 23,
    "food_name": "현미밥",
    "serving_size": "105.00",
    "calories": "250.00",
    "protein_g": "15.00",
    "carbs_g": "32.50",
    "fat_g": "1.00",
    "meal_time": null,
    "notes": "점심 추가 섭취",
    "created_at": "2025-12-04T12:00:00.000000Z",
    "updated_at": "2025-12-04T14:30:00.000000Z"
  }
}
```

**Authorization:** Users can only update their own meal logs.

**Error Responses:**
- `404 Not Found`: Meal log not found
- `403 Forbidden`: User does not have access to this meal log

---

### 4. Delete a Meal Log

Delete a meal log entry.

**Endpoint:** `DELETE /api/daily-logs/meals/{id}`

**Path Parameters:**
- `id` (required): Meal log ID

**Example Request:**
```bash
curl -X DELETE "https://api.myfit.com/api/daily-logs/meals/3" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Meal log deleted successfully",
  "data": null
}
```

**Authorization:** Users can only delete their own meal logs.

**Error Responses:**
- `404 Not Found`: Meal log not found
- `403 Forbidden`: User does not have access to this meal log

---

### 5. Get Daily Summary

Get a summary of all meals logged for a specific date, including totals and comparison with target calories.

**Endpoint:** `GET /api/daily-logs/meals/summary`

**Parameters:**
- `date` (required, string): Date in Y-m-d format

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/daily-logs/meals/summary?date=2025-12-04" \
  -H "Authorization: Bearer {token}"
```

**Example Response (with target calories):**
```json
{
  "success": true,
  "message": "Daily summary retrieved successfully",
  "data": {
    "date": "2025-12-04",
    "total_calories": 1500,
    "total_protein_g": 75,
    "total_carbs_g": 180,
    "total_fat_g": 45,
    "meal_count": 5,
    "meals_by_type": {
      "breakfast": 2,
      "lunch": 1,
      "dinner": 1,
      "snack": 1
    },
    "target_calories": 2000,
    "calories_remaining": 500,
    "percentage": 75.0
  }
}
```

**Example Response (without target calories):**
```json
{
  "success": true,
  "message": "Daily summary retrieved successfully",
  "data": {
    "date": "2025-12-04",
    "total_calories": 800,
    "total_protein_g": 40,
    "total_carbs_g": 90,
    "total_fat_g": 20,
    "meal_count": 3,
    "meals_by_type": {
      "breakfast": 1,
      "lunch": 1,
      "dinner": 0,
      "snack": 1
    }
  }
}
```

**Notes:**
- Target calories are included if the user has a calorie calculation record
- The latest calculation is used
- `calories_remaining` = target - consumed
- `percentage` = (consumed / target) * 100

---

### 6. Log Meal from Diet Plan

Quickly add a meal from the user's diet plan to their daily log.

**Endpoint:** `POST /api/daily-logs/meals/from-plan`

**Request Body:**
```json
{
  "meal_plan_item_id": 15,
  "date": "2025-12-04",
  "meal_time": "08:00"
}
```

**Validation Rules:**
- `meal_plan_item_id`: required, must exist in meal_plan_items table
- `date`: required, valid date
- `meal_time`: optional, format: H:i

**Example Request:**
```bash
curl -X POST "https://api.myfit.com/api/daily-logs/meals/from-plan" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "meal_plan_item_id": 15,
    "date": "2025-12-04",
    "meal_time": "08:00"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Meal logged from plan successfully",
  "data": {
    "id": 10,
    "user_id": 1,
    "date": "2025-12-04",
    "meal_type": "breakfast",
    "food_id": 45,
    "food_name": "닭가슴살",
    "serving_size": "100.00",
    "calories": "165.00",
    "protein_g": "31.00",
    "carbs_g": "0.00",
    "fat_g": "3.60",
    "meal_time": "08:00",
    "notes": "플랜에서 추가",
    "created_at": "2025-12-04T08:00:00.000000Z",
    "updated_at": "2025-12-04T08:00:00.000000Z"
  }
}
```

**Authorization:** Users can only log meals from their own diet plans.

**Error Responses:**
- `403 Forbidden`: User does not have access to this meal plan item
- `422 Validation Error`: Invalid meal_plan_item_id

**Notes:**
- All nutritional data is copied from the meal plan item
- Automatically adds "플랜에서 추가" (Added from plan) to notes
- Useful for quickly following the diet plan

---

## Data Models

### MealLog Model

```php
{
  "id": integer,
  "user_id": integer,
  "date": date (Y-m-d),
  "meal_type": enum (breakfast|lunch|dinner|snack),
  "food_id": integer|null,
  "food_name": string,
  "serving_size": decimal(8,2),
  "calories": decimal(8,2),
  "protein_g": decimal(8,2),
  "carbs_g": decimal(8,2),
  "fat_g": decimal(8,2),
  "meal_time": time (H:i)|null,
  "notes": text|null,
  "created_at": timestamp,
  "updated_at": timestamp
}
```

### Relationships

- **User**: Each meal log belongs to a user
- **Food**: Optional reference to food database (can be null for manual entries)

---

## Usage Examples

### Typical Daily Workflow

#### 1. Log breakfast from food database
```bash
POST /api/daily-logs/meals
{
  "date": "2025-12-04",
  "meal_type": "breakfast",
  "food_id": 15,
  "serving_size": 100,
  "meal_time": "08:00"
}
```

#### 2. Log lunch from diet plan
```bash
POST /api/daily-logs/meals/from-plan
{
  "meal_plan_item_id": 25,
  "date": "2025-12-04",
  "meal_time": "12:30"
}
```

#### 3. Log snack manually (homemade food not in database)
```bash
POST /api/daily-logs/meals
{
  "date": "2025-12-04",
  "meal_type": "snack",
  "food_name": "자몽",
  "serving_size": 150,
  "calories": 60,
  "protein_g": 1,
  "carbs_g": 15,
  "fat_g": 0,
  "meal_time": "15:00"
}
```

#### 4. Check daily progress
```bash
GET /api/daily-logs/meals/summary?date=2025-12-04
```

#### 5. View all meals for the day
```bash
GET /api/daily-logs/meals?date=2025-12-04
```

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
  "message": "You do not have access to this meal log",
  "data": null
}
```

**404 Not Found**
```json
{
  "success": false,
  "message": "Meal log not found",
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
    "meal_type": ["The meal type field is required."]
  }
}
```

---

## Business Rules

1. **Automatic Nutrient Calculation**: When logging from food database, nutrients are automatically calculated based on serving size ratio
2. **Authorization**: Users can only access their own meal logs
3. **Meal Types**: Only 4 meal types are supported (breakfast, lunch, dinner, snack)
4. **Target Comparison**: Daily summary includes target calories only if user has a calculation record
5. **Date Format**: All dates must be in Y-m-d format (e.g., "2025-12-04")
6. **Time Format**: Meal time uses H:i format (e.g., "08:30")
7. **Decimal Precision**: All nutritional values are stored and returned with 2 decimal places
8. **Serving Size Flexibility**: Users can log any serving size, system handles conversion automatically

---

## Database Schema

```sql
CREATE TABLE meal_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    date DATE NOT NULL,
    meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
    food_id BIGINT NULL,
    food_name VARCHAR(255) NOT NULL,
    serving_size DECIMAL(8,2) NOT NULL,
    calories DECIMAL(8,2) NOT NULL,
    protein_g DECIMAL(8,2) NOT NULL,
    carbs_g DECIMAL(8,2) NOT NULL,
    fat_g DECIMAL(8,2) NOT NULL,
    meal_time TIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE SET NULL,

    INDEX idx_user_id (user_id),
    INDEX idx_date (date),
    INDEX idx_user_date (user_id, date),
    INDEX idx_meal_type (meal_type)
);
```

---

## Performance Considerations

1. **Indexes**: Database indexes on user_id, date, and [user_id, date] for fast queries
2. **Date Scoping**: Always query by specific date to limit result set
3. **Grouping**: Meals are grouped by type in the application layer, not database
4. **Eager Loading**: Food relationship can be eager loaded to prevent N+1 queries

---

## Future Enhancements

- Meal photo upload support
- Barcode scanning for food lookup
- Batch meal logging
- Weekly/monthly summary reports
- Export to CSV/PDF
- Meal templates for frequently eaten meals
- Social sharing of meals
