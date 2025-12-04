<?php

namespace Tests\Feature;

use App\Models\DailyExercisePlan;
use App\Models\DailyMealPlan;
use App\Models\DietPlan;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\MealPlanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DietPlanReplacementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private DietPlan $dietPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->dietPlan = DietPlan::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function test_can_replace_meal_item_with_specific_food()
    {
        // Create original food
        $originalFood = Food::factory()->create([
            'name' => '백미밥',
            'category' => '곡류',
            'calories' => 300,
            'protein_g' => 5,
            'carbs_g' => 66,
            'fat_g' => 1,
        ]);

        // Create replacement food
        $replacementFood = Food::factory()->create([
            'name' => '현미밥',
            'category' => '곡류',
            'calories' => 280,
            'protein_g' => 6,
            'carbs_g' => 60,
            'fat_g' => 1.5,
        ]);

        // Create meal plan and item
        $dailyMealPlan = DailyMealPlan::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
        ]);

        $mealItem = MealPlanItem::factory()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
            'food_id' => $originalFood->id,
            'food_name' => $originalFood->name,
            'calories' => 300,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/diet-plans/meals/{$mealItem->id}/replace", [
                'replacement_food_id' => $replacementFood->id,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'meal_item' => [
                        'food_name' => '현미밥',
                    ],
                ],
            ]);

        // Verify database was updated
        $mealItem->refresh();
        $this->assertEquals($replacementFood->id, $mealItem->food_id);
        $this->assertEquals('현미밥', $mealItem->food_name);
    }

    /** @test */
    public function test_can_replace_meal_item_automatically()
    {
        // Create original food
        $originalFood = Food::factory()->create([
            'category' => '단백질',
            'calories' => 200,
        ]);

        // Create similar foods in same category
        Food::factory()->count(3)->create([
            'category' => '단백질',
            'calories' => 210, // Similar calories
        ]);

        $dailyMealPlan = DailyMealPlan::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
        ]);

        $mealItem = MealPlanItem::factory()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
            'food_id' => $originalFood->id,
            'calories' => 200,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/diet-plans/meals/{$mealItem->id}/replace");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'meal_item',
                    'daily_totals',
                ],
            ]);

        // Verify food was changed
        $mealItem->refresh();
        $this->assertNotEquals($originalFood->id, $mealItem->food_id);
    }

    /** @test */
    public function test_meal_replacement_recalculates_daily_totals()
    {
        $originalFood = Food::factory()->create([
            'calories' => 300,
            'protein_g' => 10,
            'carbs_g' => 50,
            'fat_g' => 5,
        ]);

        $replacementFood = Food::factory()->create([
            'calories' => 250,
            'protein_g' => 8,
            'carbs_g' => 40,
            'fat_g' => 4,
        ]);

        $dailyMealPlan = DailyMealPlan::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
            'total_calories' => 1500,
            'total_protein_g' => 100,
            'total_carbs_g' => 200,
            'total_fat_g' => 50,
        ]);

        $mealItem = MealPlanItem::factory()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
            'food_id' => $originalFood->id,
            'calories' => 300,
            'protein_g' => 10,
            'carbs_g' => 50,
            'fat_g' => 5,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/diet-plans/meals/{$mealItem->id}/replace", [
                'replacement_food_id' => $replacementFood->id,
            ]);

        $response->assertOk();

        // Verify totals were recalculated
        $dailyMealPlan->refresh();
        $this->assertNotEquals(1500, $dailyMealPlan->total_calories);
    }

    /** @test */
    public function test_can_get_meal_replacement_suggestions()
    {
        $originalFood = Food::factory()->create([
            'category' => '곡류',
            'calories' => 300,
        ]);

        // Create similar foods
        Food::factory()->count(5)->create([
            'category' => '곡류',
            'calories' => 310,
        ]);

        $dailyMealPlan = DailyMealPlan::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
        ]);

        $mealItem = MealPlanItem::factory()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
            'food_id' => $originalFood->id,
            'calories' => 300,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/diet-plans/meals/{$mealItem->id}/suggestions");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'category',
                        'calories',
                    ],
                ],
            ]);
    }

    /** @test */
    public function test_can_replace_exercise_with_specific_exercise()
    {
        $originalExercise = Exercise::factory()->create([
            'name' => '걷기',
            'category' => '유산소',
            'intensity' => '낮음',
            'calories_per_hour_per_kg' => 3.5,
        ]);

        $replacementExercise = Exercise::factory()->create([
            'name' => '조깅',
            'category' => '유산소',
            'intensity' => '보통',
            'calories_per_hour_per_kg' => 7.0,
        ]);

        $exercisePlan = DailyExercisePlan::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
            'exercise_id' => $originalExercise->id,
            'exercise_name' => $originalExercise->name,
            'duration_minutes' => 30,
            'intensity' => '낮음',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/diet-plans/exercises/{$exercisePlan->id}/replace", [
                'replacement_exercise_id' => $replacementExercise->id,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'exercise_name' => '조깅',
                    'intensity' => '보통',
                ],
            ]);

        $exercisePlan->refresh();
        $this->assertEquals($replacementExercise->id, $exercisePlan->exercise_id);
        $this->assertEquals('조깅', $exercisePlan->exercise_name);
    }

    /** @test */
    public function test_can_replace_exercise_automatically()
    {
        $originalExercise = Exercise::factory()->create([
            'category' => '유산소',
            'intensity' => '보통',
        ]);

        // Create similar exercises
        Exercise::factory()->count(3)->create([
            'category' => '유산소',
            'intensity' => '보통',
        ]);

        $exercisePlan = DailyExercisePlan::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
            'exercise_id' => $originalExercise->id,
            'intensity' => '보통',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/diet-plans/exercises/{$exercisePlan->id}/replace");

        $response->assertOk();

        $exercisePlan->refresh();
        $this->assertNotEquals($originalExercise->id, $exercisePlan->exercise_id);
    }

    /** @test */
    public function test_can_get_exercise_replacement_suggestions()
    {
        $originalExercise = Exercise::factory()->create([
            'category' => '근력',
            'intensity' => '보통',
        ]);

        Exercise::factory()->count(5)->create([
            'category' => '근력',
            'intensity' => '보통',
        ]);

        $exercisePlan = DailyExercisePlan::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
            'exercise_id' => $originalExercise->id,
            'intensity' => '보통',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/diet-plans/exercises/{$exercisePlan->id}/suggestions");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'category',
                        'intensity',
                    ],
                ],
            ]);
    }

    /** @test */
    public function test_cannot_replace_other_users_meal_item()
    {
        $otherUser = User::factory()->create();
        $otherPlan = DietPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $dailyMealPlan = DailyMealPlan::factory()->create([
            'diet_plan_id' => $otherPlan->id,
        ]);

        $mealItem = MealPlanItem::factory()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/diet-plans/meals/{$mealItem->id}/replace");

        $response->assertForbidden();
    }

    /** @test */
    public function test_cannot_replace_other_users_exercise()
    {
        $otherUser = User::factory()->create();
        $otherPlan = DietPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $exercisePlan = DailyExercisePlan::factory()->create([
            'diet_plan_id' => $otherPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/diet-plans/exercises/{$exercisePlan->id}/replace");

        $response->assertForbidden();
    }

    /** @test */
    public function test_validates_replacement_food_id()
    {
        $dailyMealPlan = DailyMealPlan::factory()->create([
            'diet_plan_id' => $this->dietPlan->id,
        ]);

        $mealItem = MealPlanItem::factory()->create([
            'daily_meal_plan_id' => $dailyMealPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/diet-plans/meals/{$mealItem->id}/replace", [
                'replacement_food_id' => 99999, // Non-existent
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['replacement_food_id']);
    }

    /** @test */
    public function test_requires_authentication_for_replacement()
    {
        $mealItem = MealPlanItem::factory()->create();

        $response = $this->putJson("/api/diet-plans/meals/{$mealItem->id}/replace");
        $response->assertUnauthorized();

        $exercisePlan = DailyExercisePlan::factory()->create();

        $response = $this->putJson("/api/diet-plans/exercises/{$exercisePlan->id}/replace");
        $response->assertUnauthorized();
    }
}
