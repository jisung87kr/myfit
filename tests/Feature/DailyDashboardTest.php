<?php

namespace Tests\Feature;

use App\Models\ExerciseLog;
use App\Models\MealLog;
use App\Models\User;
use App\Models\UserCalculation;
use App\Models\WeightLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['weight' => 70]);
        $this->authToken = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function it_can_get_dashboard_for_specific_date(): void
    {
        $date = now()->format('Y-m-d');

        // Create sample data
        MealLog::factory()->breakfast()->forDate($date)->for($this->user)->create([
            'calories' => 400,
            'protein_g' => 20,
            'carbs_g' => 50,
            'fat_g' => 10,
        ]);

        ExerciseLog::factory()->forDate($date)->for($this->user)->create([
            'duration_minutes' => 30,
            'calories_burned' => 250,
            'intensity' => '보통',
        ]);

        WeightLog::factory()->forDate($date)->for($this->user)->create([
            'weight' => 75.5,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson("/api/dashboard/show?date={$date}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'date',
                    'nutrition' => [
                        'calories_consumed',
                        'protein_g',
                        'carbs_g',
                        'fat_g',
                        'meal_count',
                        'meals_by_type',
                    ],
                    'exercise' => [
                        'calories_burned',
                        'duration_minutes',
                        'exercise_count',
                        'exercises_by_intensity',
                    ],
                    'weight',
                ],
            ])
            ->assertJson([
                'data' => [
                    'date' => $date,
                    'nutrition' => [
                        'calories_consumed' => 400,
                        'protein_g' => 20,
                        'meal_count' => 1,
                    ],
                    'exercise' => [
                        'calories_burned' => 250,
                        'duration_minutes' => 30,
                        'exercise_count' => 1,
                    ],
                    'weight' => [
                        'current_weight' => '75.50',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_includes_calorie_balance_when_target_is_available(): void
    {
        $date = now()->format('Y-m-d');

        // Create user calculation with target calories
        UserCalculation::factory()->for($this->user)->create([
            'target_calories' => 2000,
        ]);

        // Create meals and exercises
        MealLog::factory()->forDate($date)->for($this->user)->create([
            'calories' => 1500,
        ]);

        ExerciseLog::factory()->forDate($date)->for($this->user)->create([
            'calories_burned' => 300,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson("/api/dashboard/show?date={$date}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'calorie_balance' => [
                        'target_calories',
                        'calories_consumed',
                        'calories_burned',
                        'net_calories',
                        'remaining_calories',
                        'percentage_of_target',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'calorie_balance' => [
                        'target_calories' => 2000,
                        'calories_consumed' => 1500,
                        'calories_burned' => 300,
                        'net_calories' => 1200,
                        'remaining_calories' => 800,
                        'percentage_of_target' => 60.0,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_returns_empty_data_when_no_logs_exist(): void
    {
        $date = now()->format('Y-m-d');

        $response = $this->withToken($this->authToken)
            ->getJson("/api/dashboard/show?date={$date}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'nutrition' => [
                        'calories_consumed' => 0,
                        'meal_count' => 0,
                    ],
                    'exercise' => [
                        'calories_burned' => 0,
                        'exercise_count' => 0,
                    ],
                    'weight' => null,
                ],
            ]);
    }

    /** @test */
    public function it_can_get_todays_dashboard(): void
    {
        $today = now()->format('Y-m-d');

        // Create today's data
        MealLog::factory()->today()->for($this->user)->create([
            'calories' => 500,
        ]);

        ExerciseLog::factory()->today()->for($this->user)->create([
            'calories_burned' => 200,
        ]);

        WeightLog::factory()->today()->for($this->user)->create([
            'weight' => 74.0,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/today');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'date' => $today,
                    'nutrition' => [
                        'calories_consumed' => 500,
                    ],
                    'exercise' => [
                        'calories_burned' => 200,
                    ],
                    'weight' => [
                        'current_weight' => '74.00',
                        'last_updated' => $today,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_shows_latest_weight_in_todays_dashboard(): void
    {
        // Create weight from yesterday
        WeightLog::factory()->forDate(now()->subDay()->format('Y-m-d'))->for($this->user)->create([
            'weight' => 76.0,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/today');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'weight' => [
                        'current_weight' => '76.00',
                        'last_updated' => now()->subDay()->format('Y-m-d'),
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_get_weekly_summary(): void
    {
        $startDate = now()->startOfWeek()->format('Y-m-d');

        // Create data across the week
        for ($i = 0; $i < 7; $i++) {
            $date = now()->startOfWeek()->addDays($i)->format('Y-m-d');

            MealLog::factory()->forDate($date)->for($this->user)->create([
                'calories' => 1500 + ($i * 10),
                'protein_g' => 60,
                'carbs_g' => 180,
                'fat_g' => 50,
            ]);

            ExerciseLog::factory()->forDate($date)->for($this->user)->create([
                'duration_minutes' => 30,
                'calories_burned' => 250,
            ]);

            if ($i % 2 === 0) {
                WeightLog::factory()->forDate($date)->for($this->user)->create([
                    'weight' => 75 - ($i * 0.2),
                ]);
            }
        }

        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/weekly-summary');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'period' => [
                        'start_date',
                        'end_date',
                    ],
                    'nutrition' => [
                        'total_calories',
                        'total_protein_g',
                        'total_carbs_g',
                        'total_fat_g',
                        'daily_average_calories',
                        'meal_count',
                    ],
                    'exercise' => [
                        'total_calories_burned',
                        'total_duration_minutes',
                        'daily_average_calories_burned',
                        'exercise_count',
                    ],
                    'weight' => [
                        'entry_count',
                        'start_weight',
                        'end_weight',
                        'weight_change',
                    ],
                    'daily_breakdown',
                ],
            ])
            ->assertJson([
                'data' => [
                    'nutrition' => [
                        'meal_count' => 7,
                    ],
                    'exercise' => [
                        'total_duration_minutes' => 210,
                        'exercise_count' => 7,
                    ],
                    'weight' => [
                        'entry_count' => 4,
                    ],
                ],
            ]);

        // Verify daily breakdown structure
        $data = $response->json('data');
        $this->assertCount(7, $data['daily_breakdown']);
        $this->assertArrayHasKey('date', $data['daily_breakdown'][0]);
        $this->assertArrayHasKey('day_of_week', $data['daily_breakdown'][0]);
        $this->assertArrayHasKey('calories_consumed', $data['daily_breakdown'][0]);
        $this->assertArrayHasKey('calories_burned', $data['daily_breakdown'][0]);
        $this->assertArrayHasKey('net_calories', $data['daily_breakdown'][0]);
        $this->assertArrayHasKey('weight', $data['daily_breakdown'][0]);
    }

    /** @test */
    public function it_can_specify_custom_start_date_for_weekly_summary(): void
    {
        $startDate = now()->subWeek()->startOfWeek()->format('Y-m-d');

        $response = $this->withToken($this->authToken)
            ->getJson("/api/dashboard/weekly-summary?start_date={$startDate}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'period' => [
                        'start_date' => $startDate,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_get_quick_stats(): void
    {
        $today = now()->format('Y-m-d');

        // Create today's data
        MealLog::factory()->forDate($today)->for($this->user)->count(3)->create([
            'calories' => 500,
        ]);

        ExerciseLog::factory()->forDate($today)->for($this->user)->count(2)->create([
            'calories_burned' => 200,
        ]);

        // Create weight and historical data
        WeightLog::factory()->for($this->user)->create(['weight' => 75.0]);

        // Create past data for totals
        MealLog::factory()->for($this->user)->count(5)->create();
        ExerciseLog::factory()->for($this->user)->count(3)->create();

        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/quick-stats');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'today' => [
                        'calories_consumed',
                        'calories_burned',
                        'net_calories',
                    ],
                    'current_weight',
                    'logging_streak_days',
                    'total_entries' => [
                        'meals',
                        'exercises',
                        'weights',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'today' => [
                        'calories_consumed' => 1500,
                        'calories_burned' => 400,
                        'net_calories' => 1100,
                    ],
                    'current_weight' => '75.00',
                    'total_entries' => [
                        'meals' => 8, // 3 today + 5 historical
                        'exercises' => 5, // 2 today + 3 historical
                        'weights' => 1,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_calculates_logging_streak_correctly(): void
    {
        // Create consecutive daily meals
        for ($i = 0; $i < 5; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            MealLog::factory()->forDate($date)->for($this->user)->create();
        }

        // Skip a day (no meal on day 6)

        // Create meal on day 7 (should not be counted)
        MealLog::factory()->forDate(now()->subDays(6)->format('Y-m-d'))->for($this->user)->create();

        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/quick-stats');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'logging_streak_days' => 5,
                ],
            ]);
    }

    /** @test */
    public function it_returns_zero_streak_when_no_meal_today(): void
    {
        // Create meals on past days only
        MealLog::factory()->forDate(now()->subDays(2)->format('Y-m-d'))->for($this->user)->create();
        MealLog::factory()->forDate(now()->subDays(3)->format('Y-m-d'))->for($this->user)->create();

        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/quick-stats');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'logging_streak_days' => 0,
                ],
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints(): void
    {
        $date = now()->format('Y-m-d');

        // Show
        $this->getJson("/api/dashboard/show?date={$date}")
            ->assertUnauthorized();

        // Today
        $this->getJson('/api/dashboard/today')
            ->assertUnauthorized();

        // Weekly summary
        $this->getJson('/api/dashboard/weekly-summary')
            ->assertUnauthorized();

        // Quick stats
        $this->getJson('/api/dashboard/quick-stats')
            ->assertUnauthorized();
    }

    /** @test */
    public function it_only_shows_users_own_data(): void
    {
        $otherUser = User::factory()->create();
        $today = now()->format('Y-m-d');

        // Create data for both users
        MealLog::factory()->forDate($today)->for($this->user)->create(['calories' => 500]);
        MealLog::factory()->forDate($today)->for($otherUser)->create(['calories' => 1000]);

        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/today');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'nutrition' => [
                        'calories_consumed' => 500,
                        'meal_count' => 1,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_validates_date_parameter(): void
    {
        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/show?date=invalid-date');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function it_requires_date_parameter_for_show_endpoint(): void
    {
        $response = $this->withToken($this->authToken)
            ->getJson('/api/dashboard/show');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function it_aggregates_multiple_meals_and_exercises_correctly(): void
    {
        $date = now()->format('Y-m-d');

        // Create multiple meals
        MealLog::factory()->breakfast()->forDate($date)->for($this->user)->create(['calories' => 400]);
        MealLog::factory()->lunch()->forDate($date)->for($this->user)->create(['calories' => 600]);
        MealLog::factory()->dinner()->forDate($date)->for($this->user)->create(['calories' => 500]);
        MealLog::factory()->snack()->forDate($date)->for($this->user)->create(['calories' => 200]);

        // Create multiple exercises
        ExerciseLog::factory()->lowIntensity()->forDate($date)->for($this->user)->create(['calories_burned' => 150, 'duration_minutes' => 30]);
        ExerciseLog::factory()->highIntensity()->forDate($date)->for($this->user)->create(['calories_burned' => 350, 'duration_minutes' => 45]);

        $response = $this->withToken($this->authToken)
            ->getJson("/api/dashboard/show?date={$date}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'nutrition' => [
                        'calories_consumed' => 1700,
                        'meal_count' => 4,
                        'meals_by_type' => [
                            'breakfast' => 1,
                            'lunch' => 1,
                            'dinner' => 1,
                            'snack' => 1,
                        ],
                    ],
                    'exercise' => [
                        'calories_burned' => 500,
                        'duration_minutes' => 75,
                        'exercise_count' => 2,
                    ],
                ],
            ]);
    }
}
