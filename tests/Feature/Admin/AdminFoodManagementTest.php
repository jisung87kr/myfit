<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminFoodManagementTest extends TestCase
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

    public function test_admin_can_list_foods(): void
    {
        Food::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/foods');

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

    public function test_admin_can_search_foods(): void
    {
        Food::factory()->create(['name' => 'Chicken Breast']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/foods?search=chicken');

        $response->assertOk();
    }

    public function test_admin_can_filter_foods_by_category(): void
    {
        Food::factory()->create(['category' => '단백질']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/foods?category=' . urlencode('단백질'));

        $response->assertOk();
    }

    public function test_admin_can_create_food(): void
    {
        $foodData = [
            'name' => 'Test Food',
            'name_en' => 'Test Food EN',
            'category' => '단백질',
            'serving_size' => 100,
            'calories' => 200,
            'protein_g' => 25,
            'carbs_g' => 5,
            'fat_g' => 10,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/foods', $foodData);

        $response->assertCreated();
        $this->assertDatabaseHas('foods', ['name' => 'Test Food']);
    }

    public function test_admin_can_view_food(): void
    {
        $food = Food::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/foods/{$food->id}");

        $response->assertOk()
            ->assertJsonPath('data.food.id', $food->id);
    }

    public function test_admin_can_update_food(): void
    {
        $food = Food::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/foods/{$food->id}", [
                'name' => 'Updated Food Name',
                'category' => '단백질',
                'serving_size' => 100,
                'calories' => 250,
                'protein_g' => 30,
                'carbs_g' => 5,
                'fat_g' => 12,
            ]);

        $response->assertOk();
        $this->assertEquals('Updated Food Name', $food->fresh()->name);
    }

    public function test_admin_can_delete_food(): void
    {
        $food = Food::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/foods/{$food->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('foods', ['id' => $food->id]);
    }

    public function test_admin_can_bulk_import_foods(): void
    {
        $foods = [
            [
                'name' => 'Bulk Food 1',
                'category' => '단백질',
                'serving_size' => 100,
                'calories' => 150,
                'protein_g' => 20,
                'carbs_g' => 0,
                'fat_g' => 7,
            ],
            [
                'name' => 'Bulk Food 2',
                'category' => '곡류',
                'serving_size' => 100,
                'calories' => 350,
                'protein_g' => 7,
                'carbs_g' => 70,
                'fat_g' => 2,
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/foods/bulk-import', ['foods' => $foods]);

        $response->assertOk();
        $this->assertDatabaseHas('foods', ['name' => 'Bulk Food 1']);
        $this->assertDatabaseHas('foods', ['name' => 'Bulk Food 2']);
    }

    public function test_regular_user_cannot_manage_foods(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/admin/foods');

        $response->assertForbidden();
    }

    public function test_food_creation_requires_valid_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/foods', [
                'name' => '',
            ]);

        $response->assertUnprocessable();
    }
}
