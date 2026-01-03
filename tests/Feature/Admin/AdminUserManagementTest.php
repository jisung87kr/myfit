<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\MealLog;
use App\Models\ExerciseLog;
use App\Models\Food;
use App\Models\Exercise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'moderator', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users');

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

    public function test_admin_can_filter_users_by_status(): void
    {
        $disabledUser = User::factory()->create([
            'disabled_at' => now(),
            'disabled_reason' => 'Test reason',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users?status=disabled');

        $response->assertOk();
    }

    public function test_admin_can_search_users(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users?search=john');

        $response->assertOk();
    }

    public function test_admin_can_view_user_details(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/users/{$this->user->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'stats',
                ],
            ]);
    }

    public function test_admin_can_disable_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$this->user->id}/status", [
                'action' => 'disable',
                'reason' => 'Violation of terms',
            ]);

        $response->assertOk();

        $this->user->refresh();
        $this->assertNotNull($this->user->disabled_at);
        $this->assertEquals('Violation of terms', $this->user->disabled_reason);
    }

    public function test_admin_can_enable_user(): void
    {
        $this->user->update([
            'disabled_at' => now(),
            'disabled_reason' => 'Test',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$this->user->id}/status", [
                'action' => 'enable',
            ]);

        $response->assertOk();

        $this->user->refresh();
        $this->assertNull($this->user->disabled_at);
        $this->assertNull($this->user->disabled_reason);
    }

    public function test_admin_can_view_user_activity(): void
    {
        $food = Food::factory()->create();
        $exercise = Exercise::factory()->create();

        MealLog::create([
            'user_id' => $this->user->id,
            'food_id' => $food->id,
            'food_name' => $food->name,
            'meal_type' => 'breakfast',
            'serving_size' => 100,
            'calories' => 300,
            'protein_g' => 20,
            'carbs_g' => 30,
            'fat_g' => 10,
            'date' => now()->toDateString(),
        ]);

        ExerciseLog::create([
            'user_id' => $this->user->id,
            'exercise_id' => $exercise->id,
            'exercise_name' => $exercise->name,
            'duration_minutes' => 30,
            'calories_burned' => 200,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/users/{$this->user->id}/activity");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'meal_logs',
                    'exercise_logs',
                    'weight_logs',
                ],
            ]);
    }

    public function test_admin_can_assign_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$this->user->id}/roles", [
                'role' => 'moderator',
            ]);

        $response->assertOk();
        $this->assertTrue($this->user->fresh()->hasRole('moderator'));
    }

    public function test_admin_can_remove_role(): void
    {
        $this->user->assignRole('moderator');

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/users/{$this->user->id}/roles/moderator");

        $response->assertOk();
        $this->assertFalse($this->user->fresh()->hasRole('moderator'));
    }

    public function test_regular_user_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/admin/users');

        $response->assertForbidden();
    }
}
