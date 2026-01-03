<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminExerciseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
    }

    public function test_admin_can_list_exercises(): void
    {
        Exercise::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/exercises');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'total',
                ],
            ]);
    }

    public function test_admin_can_search_exercises(): void
    {
        Exercise::factory()->create(['name' => 'Running']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/exercises?search=running');

        $response->assertOk();
    }

    public function test_admin_can_filter_exercises_by_category(): void
    {
        Exercise::factory()->create(['category' => '유산소']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/exercises?category=' . urlencode('유산소'));

        $response->assertOk();
    }

    public function test_admin_can_filter_exercises_by_intensity(): void
    {
        Exercise::factory()->create(['intensity' => '높음']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/exercises?intensity=' . urlencode('높음'));

        $response->assertOk();
    }

    public function test_admin_can_create_exercise(): void
    {
        $exerciseData = [
            'name' => 'Test Exercise',
            'category' => '근력',
            'intensity' => '보통',
            'met_value' => 5.0,
            'calories_per_hour_per_kg' => 7.5,
            'description' => 'A test exercise',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/exercises', $exerciseData);

        $response->assertCreated();
        $this->assertDatabaseHas('exercises', ['name' => 'Test Exercise']);
    }

    public function test_admin_can_view_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/exercises/{$exercise->id}");

        $response->assertOk()
            ->assertJsonPath('data.exercise.id', $exercise->id);
    }

    public function test_admin_can_update_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/exercises/{$exercise->id}", [
                'name' => 'Updated Exercise Name',
                'category' => '유산소',
                'intensity' => '높음',
                'met_value' => 8.0,
                'calories_per_hour_per_kg' => 12.0,
            ]);

        $response->assertOk();
        $this->assertEquals('Updated Exercise Name', $exercise->fresh()->name);
    }

    public function test_admin_can_delete_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/exercises/{$exercise->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
    }

    public function test_admin_can_bulk_import_exercises(): void
    {
        $exercises = [
            [
                'name' => 'Bulk Exercise 1',
                'category' => '유산소',
                'intensity' => '높음',
                'met_value' => 10.0,
                'calories_per_hour_per_kg' => 15.0,
            ],
            [
                'name' => 'Bulk Exercise 2',
                'category' => '근력',
                'intensity' => '보통',
                'met_value' => 6.0,
                'calories_per_hour_per_kg' => 9.0,
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/exercises/bulk-import', ['exercises' => $exercises]);

        $response->assertOk();
        $this->assertDatabaseHas('exercises', ['name' => 'Bulk Exercise 1']);
        $this->assertDatabaseHas('exercises', ['name' => 'Bulk Exercise 2']);
    }

    public function test_regular_user_cannot_manage_exercises(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/admin/exercises');

        $response->assertForbidden();
    }

    public function test_exercise_creation_requires_valid_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/exercises', [
                'name' => '',
            ]);

        $response->assertUnprocessable();
    }
}
