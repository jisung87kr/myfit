<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_can_list_all_exercises()
    {
        Exercise::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/exercises');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'category',
                            'intensity',
                            'met_value',
                            'calories_per_hour_per_kg',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function test_can_filter_exercises_by_category()
    {
        Exercise::factory()->create(['category' => '유산소']);
        Exercise::factory()->create(['category' => '근력']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/exercises?category=유산소');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('유산소', $data[0]['category']);
    }

    /** @test */
    public function test_can_filter_exercises_by_intensity()
    {
        Exercise::factory()->create(['intensity' => '낮음']);
        Exercise::factory()->create(['intensity' => '높음']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/exercises?intensity=낮음');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('낮음', $data[0]['intensity']);
    }

    /** @test */
    public function test_can_search_exercises_by_name()
    {
        Exercise::factory()->create(['name' => '달리기']);
        Exercise::factory()->create(['name' => '걷기']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/exercises?search=달리기');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('달리기', $data[0]['name']);
    }

    /** @test */
    public function test_can_get_single_exercise()
    {
        $exercise = Exercise::factory()->create([
            'name' => '조깅',
            'met_value' => 7.0,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/exercises/{$exercise->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => '조깅',
                    'met_value' => '7.00',
                ],
            ]);
    }

    /** @test */
    public function test_can_create_new_exercise()
    {
        $exerciseData = [
            'name' => '새로운 운동',
            'category' => '유산소',
            'intensity' => '보통',
            'met_value' => 6.5,
            'calories_per_hour_per_kg' => 6.5,
            'description' => '새로운 운동 설명',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/exercises', $exerciseData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => '새로운 운동',
                    'category' => '유산소',
                ],
            ]);

        $this->assertDatabaseHas('exercises', [
            'name' => '새로운 운동',
            'category' => '유산소',
        ]);
    }

    /** @test */
    public function test_validates_exercise_creation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/exercises', [
                'name' => '', // Invalid: empty
                'category' => 'invalid', // Invalid: not in enum
                'intensity' => 'invalid', // Invalid: not in enum
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category', 'intensity', 'met_value', 'calories_per_hour_per_kg']);
    }

    /** @test */
    public function test_can_update_exercise()
    {
        $exercise = Exercise::factory()->create([
            'name' => '원래 이름',
            'met_value' => 5.0,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/exercises/{$exercise->id}", [
                'name' => '수정된 이름',
                'met_value' => 7.5,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => '수정된 이름',
                    'met_value' => '7.50',
                ],
            ]);

        $this->assertDatabaseHas('exercises', [
            'id' => $exercise->id,
            'name' => '수정된 이름',
        ]);
    }

    /** @test */
    public function test_can_delete_exercise()
    {
        $exercise = Exercise::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/exercises/{$exercise->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('exercises', [
            'id' => $exercise->id,
        ]);
    }

    /** @test */
    public function test_can_calculate_calories_for_exercise()
    {
        $exercise = Exercise::factory()->create([
            'name' => '조깅',
            'met_value' => 7.0,
            'calories_per_hour_per_kg' => 7.0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/exercises/{$exercise->id}/calculate-calories", [
                'weight_kg' => 70,
                'duration_minutes' => 30,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'exercise' => '조깅',
                    'weight_kg' => 70,
                    'duration_minutes' => 30,
                    'calories_burned' => 245.0, // 7.0 * 70 * 0.5
                    'met_value' => '7.00',
                ],
            ]);
    }

    /** @test */
    public function test_validates_calorie_calculation()
    {
        $exercise = Exercise::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/exercises/{$exercise->id}/calculate-calories", [
                'weight_kg' => 10, // Too low
                'duration_minutes' => 2000, // Too high
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight_kg', 'duration_minutes']);
    }

    /** @test */
    public function test_can_get_exercise_categories()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/exercises/categories');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['유산소', '근력', '스트레칭', '스포츠'],
            ]);
    }

    /** @test */
    public function test_can_get_exercise_intensities()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/exercises/intensities');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['낮음', '보통', '높음'],
            ]);
    }

    /** @test */
    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/exercises');

        $response->assertUnauthorized();
    }
}
