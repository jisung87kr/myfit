<?php

namespace Tests\Feature;

use App\Jobs\GenerateDietPlanJob;
use App\Models\DailyExercisePlan;
use App\Models\DailyMealPlan;
use App\Models\DietPlan;
use App\Models\MealPlanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DietPlanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_can_request_diet_plan_generation()
    {
        Queue::fake();

        $response = $this->actingAs($this->user)
            ->postJson('/api/diet-plans/generate');

        $response->assertStatus(202)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'diet_plan_id',
                    'status',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'generating',
                ],
            ]);

        Queue::assertPushed(GenerateDietPlanJob::class);
    }

    /** @test */
    public function test_cannot_generate_plan_if_active_plan_exists()
    {
        // Create an active plan
        DietPlan::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/diet-plans/generate');

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_can_check_generation_status()
    {
        $dietPlan = DietPlan::factory()->generating()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/diet-plans/generation-status/{$dietPlan->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'diet_plan_id' => $dietPlan->id,
                    'status' => 'generating',
                    'is_generating' => true,
                    'is_active' => false,
                ],
            ]);
    }

    /** @test */
    public function test_cannot_check_status_of_other_users_plan()
    {
        $otherUser = User::factory()->create();
        $dietPlan = DietPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/diet-plans/generation-status/{$dietPlan->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function test_can_get_active_diet_plan()
    {
        $dietPlan = DietPlan::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        // Create daily meal plans
        for ($day = 1; $day <= 3; $day++) {
            $dailyMealPlan = DailyMealPlan::factory()->create([
                'diet_plan_id' => $dietPlan->id,
                'day_number' => $day,
            ]);

            // Add meal items
            MealPlanItem::factory()->breakfast()->create([
                'daily_meal_plan_id' => $dailyMealPlan->id,
            ]);
            MealPlanItem::factory()->lunch()->create([
                'daily_meal_plan_id' => $dailyMealPlan->id,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->getJson('/api/diet-plans/active');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'status',
                    'start_date',
                    'end_date',
                    'target_calories_per_day',
                    'ai_summary',
                    'daily_meal_plans',
                    'daily_exercise_plans',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $dietPlan->id,
                    'status' => 'active',
                ],
            ]);
    }

    /** @test */
    public function test_returns_error_when_no_active_plan()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/diet-plans/active');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_can_get_specific_diet_plan()
    {
        $dietPlan = DietPlan::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/diet-plans/{$dietPlan->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $dietPlan->id,
                    'status' => 'active',
                ],
            ]);
    }

    /** @test */
    public function test_can_get_specific_day_plan()
    {
        $dietPlan = DietPlan::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        $dailyMealPlan = DailyMealPlan::factory()->create([
            'diet_plan_id' => $dietPlan->id,
            'day_number' => 1,
        ]);

        MealPlanItem::factory()->breakfast()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
            'food_name' => '아침 메뉴',
        ]);

        DailyExercisePlan::factory()->create([
            'diet_plan_id' => $dietPlan->id,
            'day_number' => 1,
            'exercise_name' => '조깅',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/diet-plans/{$dietPlan->id}/day/1");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'day_number',
                    'date',
                    'meals' => [
                        'breakfast',
                        'lunch',
                        'dinner',
                        'snack',
                    ],
                    'total_calories',
                    'exercises',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'day_number' => 1,
                ],
            ]);
    }

    /** @test */
    public function test_validates_day_number()
    {
        $dietPlan = DietPlan::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        // Test invalid day number (too high)
        $response = $this->actingAs($this->user)
            ->getJson("/api/diet-plans/{$dietPlan->id}/day/8");

        $response->assertStatus(400);

        // Test invalid day number (too low)
        $response = $this->actingAs($this->user)
            ->getJson("/api/diet-plans/{$dietPlan->id}/day/0");

        $response->assertStatus(400);
    }

    /** @test */
    public function test_can_regenerate_diet_plan()
    {
        Queue::fake();

        $oldPlan = DietPlan::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/diet-plans/{$oldPlan->id}/regenerate");

        $response->assertStatus(202)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'old_plan_id',
                    'new_plan_id',
                    'status',
                ],
            ]);

        // Check old plan is archived
        $this->assertDatabaseHas('diet_plans', [
            'id' => $oldPlan->id,
            'status' => 'archived',
        ]);

        Queue::assertPushed(GenerateDietPlanJob::class);
    }

    /** @test */
    public function test_diet_plan_belongs_to_user()
    {
        $dietPlan = DietPlan::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals($this->user->id, $dietPlan->user->id);
    }

    /** @test */
    public function test_diet_plan_has_daily_meal_plans()
    {
        $dietPlan = DietPlan::factory()->create();

        DailyMealPlan::factory()->count(7)->create([
            'diet_plan_id' => $dietPlan->id,
        ]);

        $this->assertCount(7, $dietPlan->dailyMealPlans);
    }

    /** @test */
    public function test_daily_meal_plan_has_meal_items()
    {
        $dailyMealPlan = DailyMealPlan::factory()->create();

        MealPlanItem::factory()->breakfast()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
        ]);
        MealPlanItem::factory()->lunch()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
        ]);
        MealPlanItem::factory()->dinner()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
        ]);

        $this->assertCount(3, $dailyMealPlan->mealItems);
    }

    /** @test */
    public function test_can_recalculate_daily_totals()
    {
        $dailyMealPlan = DailyMealPlan::factory()->create([
            'total_calories' => 0,
            'total_protein_g' => 0,
            'total_carbs_g' => 0,
            'total_fat_g' => 0,
        ]);

        MealPlanItem::factory()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
            'calories' => 300,
            'protein_g' => 20,
            'carbs_g' => 40,
            'fat_g' => 10,
        ]);

        MealPlanItem::factory()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
            'calories' => 500,
            'protein_g' => 30,
            'carbs_g' => 60,
            'fat_g' => 15,
        ]);

        $dailyMealPlan->recalculateTotals();
        $dailyMealPlan->refresh();

        $this->assertEquals(800, $dailyMealPlan->total_calories);
        $this->assertEquals(50, $dailyMealPlan->total_protein_g);
        $this->assertEquals(100, $dailyMealPlan->total_carbs_g);
        $this->assertEquals(25, $dailyMealPlan->total_fat_g);
    }

    /** @test */
    public function test_diet_plan_status_methods()
    {
        $plan = DietPlan::factory()->generating()->create();
        $this->assertTrue($plan->isGenerating());
        $this->assertFalse($plan->isActive());

        $plan->markAsActive();
        $this->assertTrue($plan->isActive());
        $this->assertFalse($plan->isGenerating());

        $plan->markAsCompleted();
        $this->assertEquals('completed', $plan->status);

        $plan->archive();
        $this->assertEquals('archived', $plan->status);
    }

    /** @test */
    public function test_requires_authentication()
    {
        $response = $this->postJson('/api/diet-plans/generate');
        $response->assertUnauthorized();

        $response = $this->getJson('/api/diet-plans/active');
        $response->assertUnauthorized();
    }
}
