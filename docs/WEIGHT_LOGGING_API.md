# Weight Logging API Documentation

## Overview

The Weight Logging API provides endpoints for users to track their body weight over time. Users can log daily weight measurements, view historical data, track progress, and analyze trends. The system automatically calculates weight changes and provides comprehensive statistics to help users monitor their weight management journey.

**Base URL:** `/api/weight-logs`

**Authentication:** All endpoints require authentication via Sanctum token.

---

## Key Features

- **Daily Weight Tracking**: Log weight once per day
- **Automatic Change Calculation**: Calculates weight change from previous entry
- **Progress Tracking**: Track overall progress from start to current weight
- **Historical Data**: View weight history for any time period
- **Statistical Analysis**: Get min/max/average weight for any date range
- **Trend Visualization**: Data formatted for easy chart creation

---

## Endpoints

### 1. Get Weight Log for Specific Date

Retrieve the weight log entry for a specific date.

**Endpoint:** `GET /api/weight-logs/show`

**Parameters:**
- `date` (required, string): Date in Y-m-d format (e.g., "2025-12-04")

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/weight-logs/show?date=2025-12-04" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Weight log retrieved successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "date": "2025-12-04",
    "weight": "75.50",
    "notes": "아침 공복 측정",
    "created_at": "2025-12-04T08:00:00.000000Z",
    "updated_at": "2025-12-04T08:00:00.000000Z"
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "No weight log found for this date",
  "data": null
}
```

---

### 2. Log Weight

Log a new weight entry for a specific date.

**Endpoint:** `POST /api/weight-logs`

**Request Body:**
```json
{
  "date": "2025-12-04",
  "weight": 75.5,
  "notes": "아침 공복 측정"
}
```

**Validation Rules:**
- `date`: required, valid date
- `weight`: required, numeric, min: 20, max: 300 (in kg)
- `notes`: optional, string, max 1000 characters

**Example Request:**
```bash
curl -X POST "https://api.myfit.com/api/weight-logs" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2025-12-04",
    "weight": 75.5,
    "notes": "아침 공복 측정"
  }'
```

**Example Response (with previous weight):**
```json
{
  "success": true,
  "message": "Weight logged successfully",
  "data": {
    "id": 5,
    "user_id": 1,
    "date": "2025-12-04",
    "weight": "75.50",
    "notes": "아침 공복 측정",
    "created_at": "2025-12-04T08:00:00.000000Z",
    "updated_at": "2025-12-04T08:00:00.000000Z",
    "weight_change": -0.5,
    "previous_weight": "76.00",
    "previous_date": "2025-12-03"
  }
}
```

**Note:** If there's a previous weight entry, the response includes:
- `weight_change`: Difference from previous weight (negative = weight loss, positive = weight gain)
- `previous_weight`: The previous weight value
- `previous_date`: Date of the previous entry

**Error Response (409 Conflict):**
```json
{
  "success": false,
  "message": "Weight log already exists for this date. Use update endpoint to modify.",
  "data": null
}
```

---

### 3. Update Weight Log

Update an existing weight log entry.

**Endpoint:** `PUT /api/weight-logs/{id}`

**Path Parameters:**
- `id` (required): Weight log ID

**Request Body:**
```json
{
  "weight": 75.0,
  "notes": "Updated measurement"
}
```

**Validation Rules:**
- `weight`: optional (but required if provided), numeric, min: 20, max: 300
- `notes`: optional, string, max 1000 characters

**Example Request:**
```bash
curl -X PUT "https://api.myfit.com/api/weight-logs/5" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "weight": 75.0,
    "notes": "재측정 - 정확한 값"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Weight log updated successfully",
  "data": {
    "id": 5,
    "user_id": 1,
    "date": "2025-12-04",
    "weight": "75.00",
    "notes": "재측정 - 정확한 값",
    "created_at": "2025-12-04T08:00:00.000000Z",
    "updated_at": "2025-12-04T14:30:00.000000Z"
  }
}
```

**Authorization:** Users can only update their own weight logs.

---

### 4. Delete Weight Log

Delete a weight log entry.

**Endpoint:** `DELETE /api/weight-logs/{id}`

**Path Parameters:**
- `id` (required): Weight log ID

**Example Request:**
```bash
curl -X DELETE "https://api.myfit.com/api/weight-logs/5" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Weight log deleted successfully",
  "data": null
}
```

**Authorization:** Users can only delete their own weight logs.

---

### 5. Get Latest Weight

Get the most recent weight log entry.

**Endpoint:** `GET /api/weight-logs/latest`

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/weight-logs/latest" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Latest weight retrieved successfully",
  "data": {
    "id": 10,
    "user_id": 1,
    "date": "2025-12-04",
    "weight": "75.00",
    "notes": null,
    "created_at": "2025-12-04T08:00:00.000000Z",
    "updated_at": "2025-12-04T08:00:00.000000Z"
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "No weight logs found",
  "data": null
}
```

---

### 6. Get Weight History

Get weight history for a specified time period.

**Endpoint:** `GET /api/weight-logs/history`

**Parameters:**
- `days` (optional, integer): Number of days to retrieve (default: 30, min: 1, max: 365)

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/weight-logs/history?days=60" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Weight history retrieved successfully",
  "data": {
    "period_days": 60,
    "entry_count": 45,
    "weights": [
      {
        "id": 1,
        "user_id": 1,
        "date": "2025-10-05",
        "weight": "80.00",
        "notes": null
      },
      {
        "id": 2,
        "user_id": 1,
        "date": "2025-10-06",
        "weight": "79.80",
        "notes": null
      },
      {
        "id": 3,
        "user_id": 1,
        "date": "2025-10-08",
        "weight": "79.50",
        "notes": "운동 시작"
      }
      // ... more entries in chronological order
    ]
  }
}
```

**Notes:**
- Returns entries in chronological order (oldest to newest)
- Perfect for creating line charts
- Includes all entries within the specified period

---

### 7. Get Weight Progress

Get overall weight progress from first entry to latest entry.

**Endpoint:** `GET /api/weight-logs/progress`

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/weight-logs/progress" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Weight progress retrieved successfully",
  "data": {
    "has_data": true,
    "start_date": "2025-10-01",
    "start_weight": "85.00",
    "latest_date": "2025-12-04",
    "latest_weight": "75.00",
    "weight_change": -10.0,
    "percentage_change": -11.76,
    "total_days": 64,
    "total_entries": 52
  }
}
```

**Field Descriptions:**
- `has_data`: Whether user has any weight logs
- `start_date`: Date of first weight entry
- `start_weight`: Weight at first entry
- `latest_date`: Date of most recent entry
- `latest_weight`: Current weight
- `weight_change`: Total weight change (negative = loss, positive = gain)
- `percentage_change`: Percentage change from start weight
- `total_days`: Days between first and latest entry
- `total_entries`: Number of weight log entries

**Error Response (404):**
```json
{
  "success": false,
  "message": "No weight logs found",
  "data": null
}
```

---

### 8. Get Statistics

Get statistical analysis for a specific date range.

**Endpoint:** `GET /api/weight-logs/statistics`

**Parameters:**
- `start_date` (required, string): Start date in Y-m-d format
- `end_date` (required, string): End date in Y-m-d format (must be after or equal to start_date)

**Example Request:**
```bash
curl -X GET "https://api.myfit.com/api/weight-logs/statistics?start_date=2025-11-01&end_date=2025-12-04" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Weight statistics retrieved successfully",
  "data": {
    "has_data": true,
    "period": {
      "start_date": "2025-11-01",
      "end_date": "2025-12-04"
    },
    "min_weight": "74.50",
    "max_weight": "78.20",
    "avg_weight": 76.15,
    "current_weight": "75.00",
    "entry_count": 28
  }
}
```

**Field Descriptions:**
- `period`: The requested date range
- `min_weight`: Lowest weight in the period
- `max_weight`: Highest weight in the period
- `avg_weight`: Average weight across all entries
- `current_weight`: Most recent weight in the period
- `entry_count`: Number of weight log entries in the period

**Error Response (404):**
```json
{
  "success": false,
  "message": "No weight logs found for the specified period",
  "data": null
}
```

---

## Data Models

### WeightLog Model

```php
{
  "id": integer,
  "user_id": integer,
  "date": date (Y-m-d),
  "weight": decimal(5,2),  // in kg
  "notes": text|null,
  "created_at": timestamp,
  "updated_at": timestamp
}
```

### Relationships

- **User**: Each weight log belongs to a user
- **Unique Constraint**: One weight log per user per date

---

## Usage Examples

### Typical Usage Flow

#### 1. Log today's weight
```bash
POST /api/weight-logs
{
  "date": "2025-12-04",
  "weight": 75.5,
  "notes": "아침 공복 체중"
}
```

#### 2. Check latest weight
```bash
GET /api/weight-logs/latest
```

#### 3. View 30-day history
```bash
GET /api/weight-logs/history?days=30
```

#### 4. Track overall progress
```bash
GET /api/weight-logs/progress
```

#### 5. Get monthly statistics
```bash
GET /api/weight-logs/statistics?start_date=2025-11-01&end_date=2025-11-30
```

### Weight Loss Tracking Example

**Week 1:**
```bash
POST /api/weight-logs
{ "date": "2025-12-01", "weight": 80.0 }
```

**Week 2:**
```bash
POST /api/weight-logs
{ "date": "2025-12-08", "weight": 79.2 }
```

**Week 3:**
```bash
POST /api/weight-logs
{ "date": "2025-12-15", "weight": 78.5 }
```

**Check Progress:**
```bash
GET /api/weight-logs/progress

Response:
{
  "weight_change": -1.5,
  "percentage_change": -1.88,
  "total_days": 14
}
```

---

## Best Practices

### Consistency in Measurement

1. **Same Time Daily**: Measure at the same time each day (ideally morning, after waking)
2. **Same Conditions**: Measure under similar conditions (e.g., before breakfast, after bathroom)
3. **Same Scale**: Use the same scale for consistency
4. **Minimal Clothing**: Wear similar minimal clothing each time

### Recommended Tracking Frequency

- **Weight Loss/Gain Goals**: Daily or every other day
- **Maintenance**: 2-3 times per week
- **Casual Monitoring**: Weekly

### Understanding Weight Fluctuations

Weight can fluctuate 1-2 kg daily due to:
- Water retention
- Food in digestive system
- Time of day
- Hormonal changes
- Exercise (muscle inflammation)

Focus on weekly averages and overall trends rather than daily changes.

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
  "message": "You do not have access to this weight log",
  "data": null
}
```

**404 Not Found**
```json
{
  "success": false,
  "message": "Weight log not found",
  "data": null
}
```

**409 Conflict (Duplicate Entry)**
```json
{
  "success": false,
  "message": "Weight log already exists for this date. Use update endpoint to modify.",
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
    "weight": ["The weight must be at least 20."]
  }
}
```

---

## Business Rules

1. **One Entry Per Day**: Users can only have one weight log per date
2. **Weight Range**: Valid weight range is 20-300 kg
3. **Authorization**: Users can only access their own weight logs
4. **Date Format**: All dates must be in Y-m-d format (e.g., "2025-12-04")
5. **Decimal Precision**: Weight is stored and returned with 2 decimal places
6. **Chronological Ordering**: History and progress data are ordered by date
7. **Automatic Calculations**: Weight changes and statistics are calculated automatically

---

## Database Schema

```sql
CREATE TABLE weight_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    date DATE NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user_id (user_id),
    INDEX idx_date (date),
    INDEX idx_user_date (user_id, date),
    UNIQUE KEY unique_user_date (user_id, date)
);
```

---

## Performance Considerations

1. **Indexes**: Optimized indexes on user_id, date, and [user_id, date]
2. **Unique Constraint**: Database-level enforcement of one entry per user per date
3. **Efficient Queries**: All queries use indexed fields for fast retrieval
4. **Calculated Fields**: Statistics calculated in application layer, no database triggers

---

## Data Visualization

The API provides data in formats optimized for common chart types:

### Line Chart (Weight Over Time)
```javascript
// Use history endpoint
GET /api/weight-logs/history?days=30

// Chart.js example
const labels = data.weights.map(w => w.date);
const values = data.weights.map(w => w.weight);
```

### Progress Bar (Goal Achievement)
```javascript
// Use progress endpoint
GET /api/weight-logs/progress

// Calculate percentage to goal
const goalWeight = 70;
const progress = (data.start_weight - data.latest_weight) /
                 (data.start_weight - goalWeight) * 100;
```

### Statistics Dashboard
```javascript
// Use statistics endpoint
GET /api/weight-logs/statistics?start_date=2025-11-01&end_date=2025-11-30

// Display min, max, avg, current
```

---

## Integration with Other Features

### Diet Plan Integration
- Weight data can influence diet plan recommendations
- Regular weight tracking helps validate diet plan effectiveness

### Daily Dashboard Integration
- Latest weight displayed on dashboard
- Quick access to recent weight trend

### Goal Setting
- Set target weight and track progress
- Receive notifications on milestones

---

## Future Enhancements

- Body composition tracking (body fat %, muscle mass)
- Multiple daily measurements support
- Photo logging alongside weight
- Goal setting with deadlines
- Milestone celebrations
- Weight prediction based on trends
- Integration with smart scales (Bluetooth)
- Export data to CSV/PDF
- Social sharing and accountability partners
- Weekly/monthly average calculations
- Custom measurement units (kg/lbs)
