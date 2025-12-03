# Food & Exercise Database API Documentation

## Overview

This document describes the API endpoints for managing foods and exercises in the MyFit application. These endpoints provide comprehensive CRUD operations for nutritional data and exercise information.

## Table of Contents

- [Food Management API](#food-management-api)
- [Exercise Management API](#exercise-management-api)
- [Data Models](#data-models)

---

## Food Management API

### List All Foods

Retrieve a paginated list of foods with optional filtering.

**Endpoint:** `GET /api/foods`

**Authentication:** Required

**Query Parameters:**
- `category` (optional): Filter by food category
- `search` (optional): Search by name (Korean or English)
- `sort_by` (optional): Field to sort by (default: 'name')
- `sort_order` (optional): 'asc' or 'desc' (default: 'asc')
- `per_page` (optional): Items per page (default: 15)

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/foods?category=단백질&per_page=20" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Foods retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "닭가슴살",
        "name_en": "Chicken Breast",
        "category": "단백질",
        "serving_size": "100.00",
        "calories": "165.00",
        "protein_g": "31.00",
        "carbs_g": "0.00",
        "fat_g": "3.60",
        "fiber_g": "0.00",
        "sodium_mg": "74.00",
        "image_url": null,
        "created_at": "2025-12-03T10:00:00.000000Z",
        "updated_at": "2025-12-03T10:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

---

### Get Single Food

Retrieve details of a specific food item.

**Endpoint:** `GET /api/foods/{id}`

**Authentication:** Required

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/foods/1" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Food retrieved successfully",
  "data": {
    "id": 1,
    "name": "닭가슴살",
    "name_en": "Chicken Breast",
    "category": "단백질",
    "serving_size": "100.00",
    "calories": "165.00",
    "protein_g": "31.00",
    "carbs_g": "0.00",
    "fat_g": "3.60",
    "fiber_g": "0.00",
    "sodium_mg": "74.00",
    "image_url": null
  }
}
```

---

### Create Food

Create a new food item in the database.

**Endpoint:** `POST /api/foods`

**Authentication:** Required

**Request Body:**
```json
{
  "name": "새로운 음식",
  "name_en": "New Food",
  "category": "곡류",
  "serving_size": 100,
  "calories": 200,
  "protein_g": 10,
  "carbs_g": 30,
  "fat_g": 5,
  "fiber_g": 2,
  "sodium_mg": 100,
  "image_url": "https://example.com/image.jpg"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `name_en`: optional, string, max 255 characters
- `category`: required, one of: 곡류, 단백질, 채소, 과일, 유제품, 견과류, 음료, 기타
- `serving_size`: required, numeric, 0.01-9999.99
- `calories`: required, numeric, 0-9999.99
- `protein_g`: required, numeric, 0-999.99
- `carbs_g`: required, numeric, 0-999.99
- `fat_g`: required, numeric, 0-999.99
- `fiber_g`: optional, numeric, 0-999.99
- `sodium_mg`: optional, numeric, 0-9999.99
- `image_url`: optional, valid URL, max 500 characters

**Example Response:**
```json
{
  "success": true,
  "message": "Food created successfully",
  "data": {
    "id": 101,
    "name": "새로운 음식",
    "category": "곡류",
    "serving_size": "100.00",
    "calories": "200.00",
    "protein_g": "10.00",
    "carbs_g": "30.00",
    "fat_g": "5.00"
  }
}
```

---

### Update Food

Update an existing food item.

**Endpoint:** `PUT /api/foods/{id}`

**Authentication:** Required

**Request Body:** (all fields optional, include only what needs to be updated)
```json
{
  "name": "수정된 이름",
  "calories": 250
}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Food updated successfully",
  "data": {
    "id": 1,
    "name": "수정된 이름",
    "calories": "250.00"
  }
}
```

---

### Delete Food

Remove a food item from the database.

**Endpoint:** `DELETE /api/foods/{id}`

**Authentication:** Required

**Example Response:**
```json
{
  "success": true,
  "message": "Food deleted successfully"
}
```

---

### Get Food Categories

Retrieve list of available food categories.

**Endpoint:** `GET /api/foods/categories`

**Authentication:** Required

**Example Response:**
```json
{
  "success": true,
  "message": "Categories retrieved successfully",
  "data": ["곡류", "단백질", "채소", "과일", "유제품", "견과류", "음료", "기타"]
}
```

---

## Exercise Management API

### List All Exercises

Retrieve a paginated list of exercises with optional filtering.

**Endpoint:** `GET /api/exercises`

**Authentication:** Required

**Query Parameters:**
- `category` (optional): Filter by exercise category
- `intensity` (optional): Filter by intensity level
- `search` (optional): Search by name
- `sort_by` (optional): Field to sort by (default: 'name')
- `sort_order` (optional): 'asc' or 'desc' (default: 'asc')
- `per_page` (optional): Items per page (default: 15)

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/exercises?category=유산소&intensity=높음" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Exercises retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "달리기",
        "category": "유산소",
        "intensity": "높음",
        "met_value": "10.00",
        "calories_per_hour_per_kg": "10.00",
        "description": "빠르게 달리기 (시속 10-12km)",
        "video_url": null,
        "created_at": "2025-12-03T10:00:00.000000Z",
        "updated_at": "2025-12-03T10:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 50
  }
}
```

---

### Get Single Exercise

Retrieve details of a specific exercise.

**Endpoint:** `GET /api/exercises/{id}`

**Authentication:** Required

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise retrieved successfully",
  "data": {
    "id": 1,
    "name": "달리기",
    "category": "유산소",
    "intensity": "높음",
    "met_value": "10.00",
    "calories_per_hour_per_kg": "10.00",
    "description": "빠르게 달리기 (시속 10-12km)"
  }
}
```

---

### Create Exercise

Create a new exercise in the database.

**Endpoint:** `POST /api/exercises`

**Authentication:** Required

**Request Body:**
```json
{
  "name": "새로운 운동",
  "category": "유산소",
  "intensity": "보통",
  "met_value": 6.5,
  "calories_per_hour_per_kg": 6.5,
  "description": "새로운 운동 설명",
  "video_url": "https://youtube.com/watch?v=example"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `category`: required, one of: 유산소, 근력, 스트레칭, 스포츠
- `intensity`: required, one of: 낮음, 보통, 높음
- `met_value`: required, numeric, 0.01-99.99
- `calories_per_hour_per_kg`: required, numeric, 0.01-9999.99
- `description`: optional, string, max 1000 characters
- `video_url`: optional, valid URL, max 500 characters

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise created successfully",
  "data": {
    "id": 51,
    "name": "새로운 운동",
    "category": "유산소",
    "intensity": "보통",
    "met_value": "6.50"
  }
}
```

---

### Update Exercise

Update an existing exercise.

**Endpoint:** `PUT /api/exercises/{id}`

**Authentication:** Required

**Request Body:** (all fields optional)
```json
{
  "name": "수정된 운동",
  "met_value": 7.5
}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise updated successfully",
  "data": {
    "id": 1,
    "name": "수정된 운동",
    "met_value": "7.50"
  }
}
```

---

### Delete Exercise

Remove an exercise from the database.

**Endpoint:** `DELETE /api/exercises/{id}`

**Authentication:** Required

**Example Response:**
```json
{
  "success": true,
  "message": "Exercise deleted successfully"
}
```

---

### Calculate Calories for Exercise

Calculate calories burned for a specific exercise based on weight and duration.

**Endpoint:** `POST /api/exercises/{id}/calculate-calories`

**Authentication:** Required

**Request Body:**
```json
{
  "weight_kg": 70,
  "duration_minutes": 30
}
```

**Validation Rules:**
- `weight_kg`: required, numeric, 20-300
- `duration_minutes`: required, numeric, 1-1440

**Calculation Formula:**
```
Calories Burned = MET Value × Weight (kg) × Duration (hours)
```

**Example Response:**
```json
{
  "success": true,
  "message": "Calories calculated successfully",
  "data": {
    "exercise": "조깅",
    "weight_kg": 70,
    "duration_minutes": 30,
    "calories_burned": 245.0,
    "met_value": "7.00"
  }
}
```

---

### Get Exercise Categories

Retrieve list of available exercise categories.

**Endpoint:** `GET /api/exercises/categories`

**Authentication:** Required

**Example Response:**
```json
{
  "success": true,
  "message": "Categories retrieved successfully",
  "data": ["유산소", "근력", "스트레칭", "스포츠"]
}
```

---

### Get Exercise Intensities

Retrieve list of available intensity levels.

**Endpoint:** `GET /api/exercises/intensities`

**Authentication:** Required

**Example Response:**
```json
{
  "success": true,
  "message": "Intensities retrieved successfully",
  "data": ["낮음", "보통", "높음"]
}
```

---

## Data Models

### Food Model

```php
{
  "id": integer,
  "name": string,              // Korean name
  "name_en": string|null,      // English name
  "category": enum,            // 곡류, 단백질, 채소, 과일, 유제품, 견과류, 음료, 기타
  "serving_size": decimal,     // grams per serving
  "calories": decimal,         // kcal
  "protein_g": decimal,        // grams
  "carbs_g": decimal,          // grams
  "fat_g": decimal,            // grams
  "fiber_g": decimal|null,     // grams
  "sodium_mg": decimal|null,   // milligrams
  "image_url": string|null,
  "created_at": timestamp,
  "updated_at": timestamp
}
```

### Exercise Model

```php
{
  "id": integer,
  "name": string,
  "category": enum,                    // 유산소, 근력, 스트레칭, 스포츠
  "intensity": enum,                   // 낮음, 보통, 높음
  "met_value": decimal,                // Metabolic Equivalent of Task
  "calories_per_hour_per_kg": decimal, // Calories burned per hour per kg
  "description": string|null,
  "video_url": string|null,
  "created_at": timestamp,
  "updated_at": timestamp
}
```

---

## MET (Metabolic Equivalent of Task) Values

MET values represent the energy cost of physical activities. 1 MET is the energy expended at rest.

**Common MET Values:**
- Light activities (2-4 METs): Walking slowly, stretching
- Moderate activities (4-6 METs): Brisk walking, light cycling
- Vigorous activities (6-8 METs): Running, swimming
- Very vigorous activities (8+ METs): Fast running, competitive sports

---

## Error Responses

All endpoints may return the following error responses:

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**404 Not Found:**
```json
{
  "message": "Resource not found."
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**500 Server Error:**
```json
{
  "success": false,
  "message": "Server error occurred"
}
```

---

## Usage Tips

1. **Pagination:** All list endpoints support pagination. Use `per_page` to control the number of items returned.

2. **Search:** Search queries work across both Korean and English names for foods, and Korean names for exercises.

3. **Filtering:** Combine multiple filters (category, intensity, search) to narrow down results.

4. **Sorting:** Use `sort_by` and `sort_order` to customize result ordering.

5. **Calorie Calculation:** The exercise calorie calculation uses scientifically validated MET values from research.

---

## Database Seed Data

The application includes comprehensive seed data:

**Foods:** 100 items across 6 categories
- 곡류 (Grains): 20 items
- 단백질 (Protein): 25 items
- 채소 (Vegetables): 20 items
- 과일 (Fruits): 15 items
- 유제품 (Dairy): 10 items
- 견과류 (Nuts): 10 items

**Exercises:** 50 items across 4 categories
- 유산소 (Cardio): 15 exercises
- 근력 (Strength): 20 exercises
- 스트레칭 (Stretching): 10 exercises
- 스포츠 (Sports): 5 exercises

---

## Next Steps

After implementing the Food & Exercise Database:
- Integrate with daily logging system (Epic 1.4)
- Connect to meal planning features (Epic 1.5)
- Implement workout recommendations (Epic 1.7)
