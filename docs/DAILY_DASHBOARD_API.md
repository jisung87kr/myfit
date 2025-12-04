# Daily Dashboard API Documentation

## Overview

The Daily Dashboard API provides comprehensive endpoints to aggregate and display user activity data from multiple sources (meals, exercises, and weight). It offers a unified view of daily progress, weekly trends, and quick statistics, making it the central hub for tracking health and fitness goals.

**Base URL:** `/api/dashboard`

**Authentication:** All endpoints require authentication via Sanctum token.

---

## Key Features

- **Unified Daily View**: Aggregate meals, exercises, and weight in one response
- **Calorie Balance**: Automatic calculation of net calories with target comparison
- **Weekly Trends**: 7-day summary with daily breakdown
- **Progress Tracking**: Logging streak and milestone tracking
- **Quick Stats**: At-a-glance overview of current status
- **Real-time Updates**: Always shows current data

---

## Endpoints

### 1. Get Dashboard for Specific Date

Get complete dashboard data for a specific date, including nutrition, exercise, and weight information.

**Endpoint:** `GET /api/dashboard/show`

**Parameters:**
- `date` (required, string): Date in Y-m-d format (e.g., "2025-12-04")

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/dashboard/show?date=2025-12-04" \
  -H "Authorization: Bearer {token}"
```

**Example Response (with target calories):**
```json
{
  "success": true,
  "message": "Daily dashboard retrieved successfully",
  "data": {
    "date": "2025-12-04",
    "nutrition": {
      "calories_consumed": 1650,
      "protein_g": 82,
      "carbs_g": 195,
      "fat_g": 55,
      "meal_count": 4,
      "meals_by_type": {
        "breakfast": 1,
        "lunch": 1,
        "dinner": 1,
        "snack": 1
      }
    },
    "exercise": {
      "calories_burned": 380,
      "duration_minutes": 60,
      "exercise_count": 2,
      "exercises_by_intensity": {
        "낮음": 0,
        "보통": 1,
        "높음": 1,
        "매우 높음": 0
      }
    },
    "weight": {
      "current_weight": "75.50",
      "notes": "아침 공복 측정"
    },
    "calorie_balance": {
      "target_calories": 2000,
      "calories_consumed": 1650,
      "calories_burned": 380,
      "net_calories": 1270,
      "remaining_calories": 730,
      "percentage_of_target": 63.5
    }
  }
}
```

**Example Response (without weight log for date):**
```json
{
  "success": true,
  "message": "Daily dashboard retrieved successfully",
  "data": {
    "date": "2025-12-04",
    "nutrition": {
      "calories_consumed": 1500,
      "protein_g": 70,
      "carbs_g": 180,
      "fat_g": 50,
      "meal_count": 3,
      "meals_by_type": {
        "breakfast": 1,
        "lunch": 1,
        "dinner": 1,
        "snack": 0
      }
    },
    "exercise": {
      "calories_burned": 250,
      "duration_minutes": 30,
      "exercise_count": 1,
      "exercises_by_intensity": {
        "낮음": 0,
        "보통": 1,
        "높음": 0,
        "매우 높음": 0
      }
    },
    "weight": null
  }
}
```

**Notes:**
- `weight` is null if no weight was logged on that specific date
- `calorie_balance` is only included if user has a UserCalculation record
- All numeric values are properly formatted with appropriate decimal places

---

### 2. Get Today's Dashboard

Get dashboard data for the current date. Shows latest weight even if not from today.

**Endpoint:** `GET /api/dashboard/today`

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/dashboard/today" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Today's dashboard retrieved successfully",
  "data": {
    "date": "2025-12-04",
    "nutrition": {
      "calories_consumed": 1200,
      "protein_g": 65,
      "carbs_g": 140,
      "fat_g": 40,
      "meal_count": 3,
      "meals_by_type": {
        "breakfast": 1,
        "lunch": 1,
        "dinner": 1,
        "snack": 0
      }
    },
    "exercise": {
      "calories_burned": 300,
      "duration_minutes": 45,
      "exercise_count": 2,
      "exercises_by_intensity": {
        "낮음": 1,
        "보통": 1,
        "높음": 0,
        "매우 높음": 0
      }
    },
    "weight": {
      "current_weight": "75.20",
      "last_updated": "2025-12-03"
    },
    "calorie_balance": {
      "target_calories": 2000,
      "calories_consumed": 1200,
      "calories_burned": 300,
      "net_calories": 900,
      "remaining_calories": 1100,
      "percentage_of_target": 45.0
    }
  }
}
```

**Notes:**
- Convenience endpoint for current day
- Weight shows latest entry with `last_updated` field
- Useful for real-time dashboard displays

---

### 3. Get Weekly Summary

Get aggregated summary for a 7-day period with daily breakdown.

**Endpoint:** `GET /api/dashboard/weekly-summary`

**Parameters:**
- `start_date` (optional, string): Week start date in Y-m-d format (defaults to current week start)

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/dashboard/weekly-summary" \
  -H "Authorization: Bearer {token}"
```

**Example Request (specific week):**
```bash
curl -X GET "https://api.myfit.com/api/dashboard/weekly-summary?start_date=2025-11-25" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Weekly summary retrieved successfully",
  "data": {
    "period": {
      "start_date": "2025-12-02",
      "end_date": "2025-12-08"
    },
    "nutrition": {
      "total_calories": 11200,
      "total_protein_g": 520,
      "total_carbs_g": 1350,
      "total_fat_g": 380,
      "daily_average_calories": 1600,
      "meal_count": 21
    },
    "exercise": {
      "total_calories_burned": 1750,
      "total_duration_minutes": 210,
      "daily_average_calories_burned": 250,
      "exercise_count": 7
    },
    "weight": {
      "entry_count": 5,
      "start_weight": "76.20",
      "end_weight": "75.50",
      "weight_change": -0.7
    },
    "daily_breakdown": [
      {
        "date": "2025-12-02",
        "day_of_week": "Monday",
        "calories_consumed": 1580,
        "calories_burned": 250,
        "net_calories": 1330,
        "weight": "76.20"
      },
      {
        "date": "2025-12-03",
        "day_of_week": "Tuesday",
        "calories_consumed": 1620,
        "calories_burned": 300,
        "net_calories": 1320,
        "weight": "76.00"
      },
      {
        "date": "2025-12-04",
        "day_of_week": "Wednesday",
        "calories_consumed": 1550,
        "calories_burned": 200,
        "net_calories": 1350,
        "weight": "75.80"
      }
      // ... continues for 7 days
    ]
  }
}
```

**Notes:**
- Always returns 7 days starting from `start_date`
- `daily_breakdown` includes all 7 days even if no data exists
- Perfect for weekly trend charts
- Weight change calculated from first to last entry in the week

---

### 4. Get Quick Stats

Get quick overview statistics including today's summary, current weight, logging streak, and total entries.

**Endpoint:** `GET /api/dashboard/quick-stats`

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/dashboard/quick-stats" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Quick stats retrieved successfully",
  "data": {
    "today": {
      "calories_consumed": 1200,
      "calories_burned": 300,
      "net_calories": 900
    },
    "current_weight": "75.50",
    "logging_streak_days": 14,
    "total_entries": {
      "meals": 245,
      "exercises": 132,
      "weights": 89
    }
  }
}
```

**Field Descriptions:**
- `today.net_calories`: Calories consumed minus calories burned for today
- `current_weight`: Latest weight entry (can be from any date)
- `logging_streak_days`: Consecutive days with at least one meal logged
- `total_entries`: Total count of all time entries

**Notes:**
- Logging streak breaks if no meal logged for a day
- Streak counts backwards from today
- Great for dashboard widgets and summary cards

---

## Data Aggregation

### Nutrition Summary

Aggregates all meal logs for the date:
```javascript
{
  "calories_consumed": sum(calories),
  "protein_g": sum(protein_g),
  "carbs_g": sum(carbs_g),
  "fat_g": sum(fat_g),
  "meal_count": count(*),
  "meals_by_type": {
    "breakfast": count(where meal_type = 'breakfast'),
    "lunch": count(where meal_type = 'lunch'),
    "dinner": count(where meal_type = 'dinner'),
    "snack": count(where meal_type = 'snack')
  }
}
```

### Exercise Summary

Aggregates all exercise logs for the date:
```javascript
{
  "calories_burned": sum(calories_burned),
  "duration_minutes": sum(duration_minutes),
  "exercise_count": count(*),
  "exercises_by_intensity": {
    "낮음": count(where intensity = '낮음'),
    "보통": count(where intensity = '보통'),
    "높음": count(where intensity = '높음'),
    "매우 높음": count(where intensity = '매우 높음')
  }
}
```

### Calorie Balance Calculation

When UserCalculation exists:
```javascript
{
  "target_calories": from UserCalculation.target_calories,
  "calories_consumed": from nutrition summary,
  "calories_burned": from exercise summary,
  "net_calories": calories_consumed - calories_burned,
  "remaining_calories": target_calories - net_calories,
  "percentage_of_target": (net_calories / target_calories) * 100
}
```

---

## Usage Examples

### Daily Tracking Workflow

#### 1. Check Today's Dashboard (Morning)
```bash
GET /api/dashboard/today

Response shows:
- 0 calories consumed (no meals yet)
- Latest weight from yesterday
- Target for the day
```

#### 2. Log Breakfast
```bash
POST /api/daily-logs/meals
{ "date": "2025-12-04", "meal_type": "breakfast", ... }
```

#### 3. Check Progress (Afternoon)
```bash
GET /api/dashboard/today

Response shows:
- Calories from breakfast
- Remaining calories for the day
- Progress percentage
```

#### 4. Log Exercise
```bash
POST /api/daily-logs/exercises
{ "date": "2025-12-04", "exercise_name": "조깅", ... }
```

#### 5. Final Check (Evening)
```bash
GET /api/dashboard/today

Response shows:
- All meals consumed
- All exercises completed
- Net calorie balance
- Achievement for the day
```

### Weekly Review Workflow

#### 1. View Weekly Summary
```bash
GET /api/dashboard/weekly-summary
```

#### 2. Analyze Trends
- Check daily breakdown for patterns
- Compare weekday vs weekend
- Identify best/worst days

#### 3. Adjust Next Week's Goals
- If consistently over/under target, adjust UserCalculation
- If low exercise days, plan more activities
- If weight not changing, review calorie balance

---

## Error Handling

### Common Error Responses

**401 Unauthorized**
```json
{
  "message": "Unauthenticated."
}
```

**422 Validation Error**
```json
{
  "success": false,
  "message": "Validation failed",
  "data": {
    "date": ["The date field is required."]
  }
}
```

---

## Business Rules

1. **Real-time Data**: All endpoints return current data from database
2. **Authorization**: Users can only access their own dashboard data
3. **Date Format**: All dates in Y-m-d format
4. **Aggregation**: Data aggregated from MealLog, ExerciseLog, WeightLog tables
5. **Calorie Balance**: Only shown when UserCalculation exists
6. **Streak Calculation**: Based on consecutive days with meal logs
7. **Empty Data**: Returns zero values when no data exists, not errors

---

## Performance Considerations

1. **Optimized Queries**: Uses model scopes and indexes
2. **Single Database Queries**: Minimal queries per request
3. **No Caching**: Always returns fresh data (can be cached at application level)
4. **Efficient Aggregation**: Uses database SUM functions
5. **Limited Date Ranges**: Weekly summary limited to 7 days

---

## Integration Points

### With Meal Logging
- Aggregates all meals for nutrition summary
- Counts meals by type
- Calculates total macronutrients

### With Exercise Logging
- Aggregates all exercises for activity summary
- Counts exercises by intensity
- Calculates total calories burned

### With Weight Logging
- Shows current/latest weight
- Displays weight from specific date when available
- Tracks weight trends in weekly summary

### With Calorie Calculations
- Uses target_calories from UserCalculation
- Calculates remaining calories
- Shows progress percentage

---

## Dashboard UI Recommendations

### Daily Dashboard Layout

```
┌─────────────────────────────────────┐
│  Today: December 4, 2025            │
├─────────────────────────────────────┤
│  CALORIE BALANCE                    │
│  ━━━━━━━━━━━━━━━━━ 63.5%          │
│  1,270 / 2,000 kcal                 │
│  730 remaining                      │
├─────────────────────────────────────┤
│  NUTRITION                          │
│  🍽️  1,650 kcal consumed          │
│  P: 82g  C: 195g  F: 55g            │
│  4 meals (B/L/D/S: 1/1/1/1)         │
├─────────────────────────────────────┤
│  EXERCISE                           │
│  🏃 380 kcal burned                 │
│  60 minutes                         │
│  2 exercises                        │
├─────────────────────────────────────┤
│  WEIGHT                             │
│  ⚖️  75.5 kg                        │
│  Logged today                       │
└─────────────────────────────────────┘
```

### Weekly Summary Layout

```
┌─────────────────────────────────────┐
│  Week: Dec 2 - Dec 8, 2025          │
├─────────────────────────────────────┤
│  WEEKLY TOTALS                      │
│  Calories: 11,200 (avg 1,600/day)   │
│  Exercise: 1,750 kcal burned        │
│  Weight: -0.7 kg                    │
├─────────────────────────────────────┤
│  DAILY TREND                        │
│  📊 [Line chart]                    │
│     Net calories per day            │
└─────────────────────────────────────┘
```

### Quick Stats Widgets

```
┌─────────┐ ┌─────────┐ ┌─────────┐
│ TODAY   │ │ WEIGHT  │ │ STREAK  │
│ 900 net │ │ 75.5 kg │ │ 14 days │
│ kcal    │ │         │ │ 🔥      │
└─────────┘ └─────────┘ └─────────┘
```

---

## Chart Recommendations

### Daily Calorie Balance (Gauge Chart)
```javascript
// Use calorie_balance data
const percentage = data.calorie_balance.percentage_of_target;
const remaining = data.calorie_balance.remaining_calories;
```

### Weekly Trend (Line Chart)
```javascript
// Use daily_breakdown from weekly summary
const labels = daily_breakdown.map(d => d.day_of_week);
const netCalories = daily_breakdown.map(d => d.net_calories);
const weights = daily_breakdown.map(d => d.weight);
```

### Macronutrient Distribution (Pie Chart)
```javascript
// Use nutrition data
const protein = nutrition.protein_g * 4; // kcal
const carbs = nutrition.carbs_g * 4; // kcal
const fat = nutrition.fat_g * 9; // kcal
```

### Exercise Intensity (Bar Chart)
```javascript
// Use exercises_by_intensity
const intensities = ['낮음', '보통', '높음', '매우 높음'];
const counts = intensities.map(i =>
  exercise.exercises_by_intensity[i]
);
```

---

## Future Enhancements

- Monthly summary endpoint
- Goal achievement tracking
- Comparative analysis (this week vs last week)
- Personalized recommendations based on patterns
- Meal timing analysis
- Exercise efficiency metrics
- BMI and body composition tracking
- Hydration tracking integration
- Sleep tracking integration
- Mood tracking correlation
- Social comparison (anonymized)
- Achievement badges
- Streak milestones
- Custom date range summaries
- Export dashboard as PDF
- Scheduled email reports
