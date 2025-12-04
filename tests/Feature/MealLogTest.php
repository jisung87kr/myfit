<?php

namespace Tests\Feature;

use App\Models\DailyMealPlan;
use App\Models\DietPlan;
use App\Models\Food;
use App\Models\MealLog;
use App\Models\MealPlanItem;
use App\Models\User;
use App\Models\UserCalculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->authToken = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function it_can_get_meal_logs_for_a_specific_date(): void
    {
        $date = now()->format('Y-m-d');

        // Create meals for the specified date
        MealLog::factory()->breakfast()->forDate($date)->for($this->user)->create();
        MealLog::factory()->lunch()->forDate($date)->for($this->user)->create();
        MealLog::factory()->dinner()->forDate($date)->for($this->user)->create();

        // Create a meal for another date (should not appear)
        MealLog::factory()->forDate(now()->subDay()->format('Y-m-d'))->for($this->user)->create();

        $response = $this->withToken($this->authToken)
            ->getJson("/api/daily-logs/meals?date={$date}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'date',
                    'meals' => [
                        'breakfast',
                        'lunch',
                        'dinner',
                        'snack',
                    ],
                    'total_count',
                ],
            ])
            ->assertJson([
                'data' => [
                    'date' => $date,
                    'total_count' => 3,
                ],
            ]);
    }

    /** @test */
    public function it_requires_date_when_getting_meal_logs(): void
    {
        $response = $this->withToken($this->authToken)
            ->getJson('/api/daily-logs/meals');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function it_can_log_a_meal_manually(): void
    {
        $date = now()->format('Y-m-d');

        $mealData = [
            'date' => $date,
            'meal_type' => 'breakfast',
            'food_name' => '계란 프라이',
            'serving_size' => 100,
            'calories' => 150,
            'protein_g' => 12,
            'carbs_g' => 2,
            'fat_g' => 10,
            'meal_time' => '08:30',
            'notes' => '아침 식사',
        ];

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/meals', $mealData);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'date',
                    'meal_type',
                    'food_name',
                    'serving_size',
                    'calories',
                    'protein_g',
                    'carbs_g',
                    'fat_g',
                    'meal_time',
                    'notes',
                ],
            ])
            ->assertJson([
                'data' => [
                    'food_name' => '계란 프라이',
                    'meal_type' => 'breakfast',
                ],
            ]);

        $this->assertDatabaseHas('meal_logs', [
            'user_id' => $this->user->id,
            'food_name' => '계란 프라이',
            'calories' => 150,
        ]);
    }

    /** @test */
    public function it_can_log_a_meal_from_food_database(): void
    {
        $food = Food::factory()->create([
            'name' => '현미밥',
            'serving_size' => 210,
            'calories' => 300,
            'protein_g' => 6,
            'carbs_g' => 65,
            'fat_g' => 2,
        ]);

        $date = now()->format('Y-m-d');

        $mealData = [
            'date' => $date,
            'meal_type' => 'lunch',
            'food_id' => $food->id,
            'serving_size' => 105, // Half serving
        ];

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/meals', $mealData);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'food_name' => '현미밥',
                    'serving_size' => '105.00',
                    'calories' => '150.00', // 300 * 0.5
                    'protein_g' => '3.00',  // 6 * 0.5
                    'carbs_g' => '32.50',   // 65 * 0.5
                    'fat_g' => '1.00',      // 2 * 0.5
                ],
            ]);

        $this->assertDatabaseHas('meal_logs', [
            'user_id' => $this->user->id,
            'food_id' => $food->id,
            'food_name' => '현미밥',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_logging_meal(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/meals', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'date',
                'meal_type',
                'serving_size',
            ]);
    }

    /** @test */
    public function it_validates_meal_type(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/meals', [
                'date' => now()->format('Y-m-d'),
                'meal_type' => 'invalid_meal_type',
                'food_name' => 'Test Food',
                'serving_size' => 100,
                'calories' => 200,
                'protein_g' => 10,
                'carbs_g' => 20,
                'fat_g' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['meal_type']);
    }

    /** @test */
    public function it_can_update_a_meal_log(): void
    {
        $mealLog = MealLog::factory()->for($this->user)->create([
            'calories' => 200,
            'notes' => 'Original note',
        ]);

        $updateData = [
            'calories' => 250,
            'protein_g' => 15,
            'notes' => 'Updated note',
        ];

        $response = $this->withToken($this->authToken)
            ->putJson("/api/daily-logs/meals/{$mealLog->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'calories' => '250.00',
                    'protein_g' => '15.00',
                    'notes' => 'Updated note',
                ],
            ]);

        $this->assertDatabaseHas('meal_logs', [
            'id' => $mealLog->id,
            'calories' => 250,
            'notes' => 'Updated note',
        ]);
    }

    /** @test */
    public function it_cannot_update_another_users_meal_log(): void
    {
        $otherUser = User::factory()->create();
        $mealLog = MealLog::factory()->for($otherUser)->create();

        $response = $this->withToken($this->authToken)
            ->putJson("/api/daily-logs/meals/{$mealLog->id}", [
                'calories' => 999,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('meal_logs', [
            'id' => $mealLog->id,
            'calories' => 999,
        ]);
    }

    /** @test */
    public function it_can_delete_a_meal_log(): void
    {
        $mealLog = MealLog::factory()->for($this->user)->create();

        $response = $this->withToken($this->authToken)
            ->deleteJson("/api/daily-logs/meals/{$mealLog->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('meal_logs', [
            'id' => $mealLog->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_another_users_meal_log(): void
    {
        $otherUser = User::factory()->create();
        $mealLog = MealLog::factory()->for($otherUser)->create();

        $response = $this->withToken($this->authToken)
            ->deleteJson("/api/daily-logs/meals/{$mealLog->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('meal_logs', [
            'id' => $mealLog->id,
        ]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_meal_log(): void
    {
        $response = $this->withToken($this->authToken)
            ->deleteJson('/api/daily-logs/meals/99999');

        $response->assertNotFound();
    }

    /** @test */
    public function it_can_get_daily_summary(): void
    {
        $date = now()->format('Y-m-d');

        MealLog::factory()->breakfast()->forDate($date)->for($this->user)->create([
            'calories' => 400,
            'protein_g' => 20,
            'carbs_g' => 50,
            'fat_g' => 10,
        ]);

        MealLog::factory()->lunch()->forDate($date)->for($this->user)->create([
            'calories' => 600,
            'protein_g' => 30,
            'carbs_g' => 70,
            'fat_g' => 20,
        ]);

        MealLog::factory()->dinner()->forDate($date)->for($this->user)->create([
            'calories' => 500,
            'protein_g' => 25,
            'carbs_g' => 60,
            'fat_g' => 15,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson("/api/daily-logs/meals/summary?date={$date}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'date',
                    'total_calories',
                    'total_protein_g',
                    'total_carbs_g',
                    'total_fat_g',
                    'meal_count',
                    'meals_by_type',
                ],
            ])
            ->assertJson([
                'data' => [
                    'date' => $date,
                    'total_calories' => 1500,
                    'total_protein_g' => 75,
                    'total_carbs_g' => 180,
                    'total_fat_g' => 45,
                    'meal_count' => 3,
                ],
            ]);
    }

    /** @test */
    public function it_includes_target_calories_in_summary_when_available(): void
    {
        $date = now()->format('Y-m-d');

        // Create user calculation with target calories
        UserCalculation::factory()->for($this->user)->create([
            'target_calories' => 2000,
            'calculated_at' => now(),
        ]);

        MealLog::factory()->breakfast()->forDate($date)->for($this->user)->create([
            'calories' => 500,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson("/api/daily-logs/meals/summary?date={$date}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'target_calories',
                    'calories_remaining',
                    'percentage',
                ],
            ])
            ->assertJson([
                'data' => [
                    'target_calories' => 2000,
                    'calories_remaining' => 1500,
                    'percentage' => 25.0,
                ],
            ]);
    }

    /** @test */
    public function it_can_log_meal_from_diet_plan(): void
    {
        $food = Food::factory()->create();

        // Create diet plan structure
        $dietPlan = DietPlan::factory()->active()->for($this->user)->create();
        $dailyMealPlan = DailyMealPlan::factory()->for($dietPlan)->create();
        $mealPlanItem = MealPlanItem::factory()
            ->for($dailyMealPlan, 'dailyMealPlan')
            ->for($food)
            ->create([
                'meal_type' => 'breakfast',
                'food_name' => '닭가슴살',
                'serving_size' => 100,
                'calories' => 165,
                'protein_g' => 31,
                'carbs_g' => 0,
                'fat_g' => 3.6,
            ]);

        $date = now()->format('Y-m-d');

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/meals/from-plan', [
                'meal_plan_item_id' => $mealPlanItem->id,
                'date' => $date,
                'meal_time' => '08:00',
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user_id' => $this->user->id,
                    'date' => $date,
                    'meal_type' => 'breakfast',
                    'food_name' => '닭가슴살',
                    'serving_size' => '100.00',
                    'calories' => '165.00',
                    'protein_g' => '31.00',
                    'notes' => '플랜에서 추가',
                ],
            ]);

        $this->assertDatabaseHas('meal_logs', [
            'user_id' => $this->user->id,
            'food_name' => '닭가슴살',
            'notes' => '플랜에서 추가',
        ]);
    }

    /** @test */
    public function it_cannot_log_from_another_users_diet_plan(): void
    {
        $otherUser = User::factory()->create();
        $food = Food::factory()->create();

        // Create diet plan for another user
        $dietPlan = DietPlan::factory()->active()->for($otherUser)->create();
        $dailyMealPlan = DailyMealPlan::factory()->for($dietPlan)->create();
        $mealPlanItem = MealPlanItem::factory()
            ->for($dailyMealPlan, 'dailyMealPlan')
            ->for($food)
            ->create();

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/meals/from-plan', [
                'meal_plan_item_id' => $mealPlanItem->id,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function it_validates_meal_plan_item_exists(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/meals/from-plan', [
                'meal_plan_item_id' => 99999,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['meal_plan_item_id']);
    }

    /** @test */
    public function it_groups_meals_by_type_correctly(): void
    {
        $date = now()->format('Y-m-d');

        // Create 2 breakfast meals
        MealLog::factory()->breakfast()->forDate($date)->for($this->user)->count(2)->create();

        // Create 1 lunch meal
        MealLog::factory()->lunch()->forDate($date)->for($this->user)->create();

        // Create 3 snack meals
        MealLog::factory()->snack()->forDate($date)->for($this->user)->count(3)->create();

        $response = $this->withToken($this->authToken)
            ->getJson("/api/daily-logs/meals?date={$date}");

        $response->assertOk();

        $data = $response->json('data');

        $this->assertCount(2, $data['meals']['breakfast']);
        $this->assertCount(1, $data['meals']['lunch']);
        $this->assertCount(0, $data['meals']['dinner']);
        $this->assertCount(3, $data['meals']['snack']);
        $this->assertEquals(6, $data['total_count']);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints(): void
    {
        $date = now()->format('Y-m-d');

        // Index
        $this->getJson("/api/daily-logs/meals?date={$date}")
            ->assertUnauthorized();

        // Store
        $this->postJson('/api/daily-logs/meals', [])
            ->assertUnauthorized();

        // Update
        $this->putJson('/api/daily-logs/meals/1', [])
            ->assertUnauthorized();

        // Delete
        $this->deleteJson('/api/daily-logs/meals/1')
            ->assertUnauthorized();

        // Summary
        $this->getJson("/api/daily-logs/meals/summary?date={$date}")
            ->assertUnauthorized();

        // Log from plan
        $this->postJson('/api/daily-logs/meals/from-plan', [])
            ->assertUnauthorized();
    }

    /** @test */
    public function it_can_get_empty_summary_for_date_with_no_meals(): void
    {
        $date = now()->format('Y-m-d');

        $response = $this->withToken($this->authToken)
            ->getJson("/api/daily-logs/meals/summary?date={$date}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'date' => $date,
                    'total_calories' => 0,
                    'total_protein_g' => 0,
                    'total_carbs_g' => 0,
                    'total_fat_g' => 0,
                    'meal_count' => 0,
                ],
            ]);
    }

    /** @test */
    public function it_handles_decimal_values_correctly(): void
    {
        $food = Food::factory()->create([
            'serving_size' => 100,
            'calories' => 123.45,
            'protein_g' => 12.34,
            'carbs_g' => 23.45,
            'fat_g' => 3.45,
        ]);

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/meals', [
                'date' => now()->format('Y-m-d'),
                'meal_type' => 'breakfast',
                'food_id' => $food->id,
                'serving_size' => 150, // 1.5x serving
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'calories' => '185.18',    // 123.45 * 1.5
                    'protein_g' => '18.51',    // 12.34 * 1.5
                    'carbs_g' => '35.18',      // 23.45 * 1.5
                    'fat_g' => '5.18',         // 3.45 * 1.5
                ],
            ]);
    }
}
