<?php

namespace Tests\Feature;

use App\Models\DailyExercisePlan;
use App\Models\DietPlan;
use App\Models\Exercise;
use App\Models\ExerciseLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseLogTest extends TestCase
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
    public function it_can_get_exercise_logs_for_a_specific_date(): void
    {
        $date = now()->format('Y-m-d');

        // Create exercises for the specified date
        ExerciseLog::factory()->forDate($date)->for($this->user)->count(3)->create();

        // Create an exercise for another date (should not appear)
        ExerciseLog::factory()->forDate(now()->subDay()->format('Y-m-d'))->for($this->user)->create();

        $response = $this->withToken($this->authToken)
            ->getJson("/api/daily-logs/exercises?date={$date}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'date',
                    'exercises',
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
    public function it_requires_date_when_getting_exercise_logs(): void
    {
        $response = $this->withToken($this->authToken)
            ->getJson('/api/daily-logs/exercises');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function it_can_log_an_exercise_manually(): void
    {
        $date = now()->format('Y-m-d');

        $exerciseData = [
            'date' => $date,
            'exercise_name' => '조깅',
            'duration_minutes' => 30,
            'calories_burned' => 250,
            'intensity' => '보통',
            'exercise_time' => '07:00',
            'notes' => '아침 운동',
        ];

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises', $exerciseData);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'date',
                    'exercise_name',
                    'duration_minutes',
                    'calories_burned',
                    'intensity',
                    'exercise_time',
                    'notes',
                ],
            ])
            ->assertJson([
                'data' => [
                    'exercise_name' => '조깅',
                    'duration_minutes' => 30,
                ],
            ]);

        $this->assertDatabaseHas('exercise_logs', [
            'user_id' => $this->user->id,
            'exercise_name' => '조깅',
            'calories_burned' => 250,
        ]);
    }

    /** @test */
    public function it_can_log_an_exercise_from_exercise_database(): void
    {
        $exercise = Exercise::factory()->create([
            'name' => '수영',
            'intensity' => '높음',
            'met_value' => 8.0,
        ]);

        $date = now()->format('Y-m-d');

        $exerciseData = [
            'date' => $date,
            'exercise_id' => $exercise->id,
            'duration_minutes' => 60,
        ];

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises', $exerciseData);

        // Expected calories: 8.0 (MET) * 70 (kg) * 1 (hour) = 560
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'exercise_name' => '수영',
                    'intensity' => '높음',
                    'duration_minutes' => 60,
                    'calories_burned' => '560.00',
                ],
            ]);

        $this->assertDatabaseHas('exercise_logs', [
            'user_id' => $this->user->id,
            'exercise_id' => $exercise->id,
            'exercise_name' => '수영',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_logging_exercise(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'date',
                'duration_minutes',
            ]);
    }

    /** @test */
    public function it_validates_intensity_values(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises', [
                'date' => now()->format('Y-m-d'),
                'exercise_name' => 'Test Exercise',
                'duration_minutes' => 30,
                'calories_burned' => 200,
                'intensity' => 'invalid_intensity',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['intensity']);
    }

    /** @test */
    public function it_can_update_an_exercise_log(): void
    {
        $exerciseLog = ExerciseLog::factory()->for($this->user)->create([
            'duration_minutes' => 30,
            'notes' => 'Original note',
        ]);

        $updateData = [
            'duration_minutes' => 45,
            'calories_burned' => 300,
            'notes' => 'Updated note',
        ];

        $response = $this->withToken($this->authToken)
            ->putJson("/api/daily-logs/exercises/{$exerciseLog->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'duration_minutes' => 45,
                    'calories_burned' => '300.00',
                    'notes' => 'Updated note',
                ],
            ]);

        $this->assertDatabaseHas('exercise_logs', [
            'id' => $exerciseLog->id,
            'duration_minutes' => 45,
            'notes' => 'Updated note',
        ]);
    }

    /** @test */
    public function it_cannot_update_another_users_exercise_log(): void
    {
        $otherUser = User::factory()->create();
        $exerciseLog = ExerciseLog::factory()->for($otherUser)->create();

        $response = $this->withToken($this->authToken)
            ->putJson("/api/daily-logs/exercises/{$exerciseLog->id}", [
                'duration_minutes' => 999,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('exercise_logs', [
            'id' => $exerciseLog->id,
            'duration_minutes' => 999,
        ]);
    }

    /** @test */
    public function it_can_delete_an_exercise_log(): void
    {
        $exerciseLog = ExerciseLog::factory()->for($this->user)->create();

        $response = $this->withToken($this->authToken)
            ->deleteJson("/api/daily-logs/exercises/{$exerciseLog->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('exercise_logs', [
            'id' => $exerciseLog->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_another_users_exercise_log(): void
    {
        $otherUser = User::factory()->create();
        $exerciseLog = ExerciseLog::factory()->for($otherUser)->create();

        $response = $this->withToken($this->authToken)
            ->deleteJson("/api/daily-logs/exercises/{$exerciseLog->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('exercise_logs', [
            'id' => $exerciseLog->id,
        ]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_exercise_log(): void
    {
        $response = $this->withToken($this->authToken)
            ->deleteJson('/api/daily-logs/exercises/99999');

        $response->assertNotFound();
    }

    /** @test */
    public function it_can_get_daily_summary(): void
    {
        $date = now()->format('Y-m-d');

        ExerciseLog::factory()->lowIntensity()->forDate($date)->for($this->user)->create([
            'duration_minutes' => 30,
            'calories_burned' => 150,
            'intensity' => '낮음',
        ]);

        ExerciseLog::factory()->mediumIntensity()->forDate($date)->for($this->user)->create([
            'duration_minutes' => 45,
            'calories_burned' => 300,
            'intensity' => '보통',
        ]);

        ExerciseLog::factory()->highIntensity()->forDate($date)->for($this->user)->create([
            'duration_minutes' => 25,
            'calories_burned' => 250,
            'intensity' => '높음',
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson("/api/daily-logs/exercises/summary?date={$date}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'date',
                    'total_duration_minutes',
                    'total_calories_burned',
                    'exercise_count',
                    'exercises_by_intensity',
                ],
            ])
            ->assertJson([
                'data' => [
                    'date' => $date,
                    'total_duration_minutes' => 100,
                    'total_calories_burned' => 700,
                    'exercise_count' => 3,
                ],
            ]);
    }

    /** @test */
    public function it_can_log_exercise_from_diet_plan(): void
    {
        $exercise = Exercise::factory()->create();

        // Create diet plan structure
        $dietPlan = DietPlan::factory()->active()->for($this->user)->create();
        $dailyExercisePlan = DailyExercisePlan::factory()
            ->for($dietPlan)
            ->for($exercise)
            ->create([
                'exercise_name' => '조깅',
                'duration_minutes' => 30,
                'estimated_calories_burned' => 245,
                'intensity' => '보통',
            ]);

        $date = now()->format('Y-m-d');

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises/from-plan', [
                'daily_exercise_plan_id' => $dailyExercisePlan->id,
                'date' => $date,
                'exercise_time' => '07:00',
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user_id' => $this->user->id,
                    'date' => $date,
                    'exercise_name' => '조깅',
                    'duration_minutes' => 30,
                    'calories_burned' => '245.00',
                    'intensity' => '보통',
                    'notes' => '플랜에서 추가',
                ],
            ]);

        $this->assertDatabaseHas('exercise_logs', [
            'user_id' => $this->user->id,
            'exercise_name' => '조깅',
            'notes' => '플랜에서 추가',
        ]);
    }

    /** @test */
    public function it_cannot_log_from_another_users_diet_plan(): void
    {
        $otherUser = User::factory()->create();
        $exercise = Exercise::factory()->create();

        // Create diet plan for another user
        $dietPlan = DietPlan::factory()->active()->for($otherUser)->create();
        $dailyExercisePlan = DailyExercisePlan::factory()
            ->for($dietPlan)
            ->for($exercise)
            ->create();

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises/from-plan', [
                'daily_exercise_plan_id' => $dailyExercisePlan->id,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function it_validates_daily_exercise_plan_exists(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises/from-plan', [
                'daily_exercise_plan_id' => 99999,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['daily_exercise_plan_id']);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints(): void
    {
        $date = now()->format('Y-m-d');

        // Index
        $this->getJson("/api/daily-logs/exercises?date={$date}")
            ->assertUnauthorized();

        // Store
        $this->postJson('/api/daily-logs/exercises', [])
            ->assertUnauthorized();

        // Update
        $this->putJson('/api/daily-logs/exercises/1', [])
            ->assertUnauthorized();

        // Delete
        $this->deleteJson('/api/daily-logs/exercises/1')
            ->assertUnauthorized();

        // Summary
        $this->getJson("/api/daily-logs/exercises/summary?date={$date}")
            ->assertUnauthorized();

        // Log from plan
        $this->postJson('/api/daily-logs/exercises/from-plan', [])
            ->assertUnauthorized();
    }

    /** @test */
    public function it_can_get_empty_summary_for_date_with_no_exercises(): void
    {
        $date = now()->format('Y-m-d');

        $response = $this->withToken($this->authToken)
            ->getJson("/api/daily-logs/exercises/summary?date={$date}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'date' => $date,
                    'total_duration_minutes' => 0,
                    'total_calories_burned' => 0,
                    'exercise_count' => 0,
                ],
            ]);
    }

    /** @test */
    public function it_calculates_calories_correctly_with_met_value(): void
    {
        $exercise = Exercise::factory()->create([
            'met_value' => 6.0,
        ]);

        // User weight is 70kg, duration is 30 minutes (0.5 hours)
        // Expected calories: 6.0 * 70 * 0.5 = 210

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises', [
                'date' => now()->format('Y-m-d'),
                'exercise_id' => $exercise->id,
                'duration_minutes' => 30,
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'calories_burned' => '210.00',
                ],
            ]);
    }

    /** @test */
    public function it_handles_decimal_values_correctly(): void
    {
        $exercise = Exercise::factory()->create([
            'met_value' => 7.5,
        ]);

        // User weight is 70kg, duration is 45 minutes (0.75 hours)
        // Expected calories: 7.5 * 70 * 0.75 = 393.75

        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises', [
                'date' => now()->format('Y-m-d'),
                'exercise_id' => $exercise->id,
                'duration_minutes' => 45,
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'calories_burned' => '393.75',
                ],
            ]);
    }

    /** @test */
    public function it_validates_duration_minimum_value(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/daily-logs/exercises', [
                'date' => now()->format('Y-m-d'),
                'exercise_name' => 'Test',
                'duration_minutes' => 0,
                'calories_burned' => 100,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['duration_minutes']);
    }
}
