<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Food;
use App\Models\Exercise;
use App\Models\DietPlan;
use App\Models\MealLog;
use App\Models\ExerciseLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        // Create regular user
        $this->user = User::factory()->create();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'users',
                    'activity',
                    'diet_plans',
                    'community',
                ],
            ]);
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->getJson('/api/admin/dashboard');

        $response->assertUnauthorized();
    }

    public function test_admin_can_access_user_stats(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/stats/users');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'today',
                    'this_week',
                    'this_month',
                    'growth',
                ],
            ]);
    }

    public function test_admin_can_access_activity_stats(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/stats/activity');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'meal_logs_today',
                    'exercise_logs_today',
                    'total_meal_logs',
                    'trends',
                ],
            ]);
    }
}
