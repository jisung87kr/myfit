<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_can_list_all_foods()
    {
        Food::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/foods');

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
                            'calories',
                            'protein_g',
                            'carbs_g',
                            'fat_g',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function test_can_filter_foods_by_category()
    {
        Food::factory()->create(['category' => '곡류']);
        Food::factory()->create(['category' => '단백질']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/foods?category=곡류');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('곡류', $data[0]['category']);
    }

    /** @test */
    public function test_can_search_foods_by_name()
    {
        Food::factory()->create(['name' => '백미밥', 'name_en' => 'White Rice']);
        Food::factory()->create(['name' => '현미밥', 'name_en' => 'Brown Rice']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/foods?search=백미');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('백미', $data[0]['name']);
    }

    /** @test */
    public function test_can_get_single_food()
    {
        $food = Food::factory()->create([
            'name' => '닭가슴살',
            'calories' => 165,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/foods/{$food->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => '닭가슴살',
                    'calories' => '165.00',
                ],
            ]);
    }

    /** @test */
    public function test_can_create_new_food()
    {
        $foodData = [
            'name' => '새로운 음식',
            'name_en' => 'New Food',
            'category' => '곡류',
            'serving_size' => 100,
            'calories' => 200,
            'protein_g' => 10,
            'carbs_g' => 30,
            'fat_g' => 5,
            'fiber_g' => 2,
            'sodium_mg' => 100,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/foods', $foodData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => '새로운 음식',
                    'category' => '곡류',
                ],
            ]);

        $this->assertDatabaseHas('foods', [
            'name' => '새로운 음식',
            'category' => '곡류',
        ]);
    }

    /** @test */
    public function test_validates_food_creation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/foods', [
                'name' => '', // Invalid: empty
                'category' => 'invalid', // Invalid: not in enum
                'calories' => -10, // Invalid: negative
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category', 'serving_size', 'calories', 'protein_g', 'carbs_g', 'fat_g']);
    }

    /** @test */
    public function test_can_update_food()
    {
        $food = Food::factory()->create([
            'name' => '원래 이름',
            'calories' => 100,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/foods/{$food->id}", [
                'name' => '수정된 이름',
                'calories' => 150,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => '수정된 이름',
                    'calories' => '150.00',
                ],
            ]);

        $this->assertDatabaseHas('foods', [
            'id' => $food->id,
            'name' => '수정된 이름',
        ]);
    }

    /** @test */
    public function test_can_delete_food()
    {
        $food = Food::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/foods/{$food->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('foods', [
            'id' => $food->id,
        ]);
    }

    /** @test */
    public function test_can_get_food_categories()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/foods/categories');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['곡류', '단백질', '채소', '과일', '유제품', '견과류', '음료', '기타'],
            ]);
    }

    /** @test */
    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/foods');

        $response->assertUnauthorized();
    }
}
