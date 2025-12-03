<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\UserSurveyResponse;
use App\Enums\QuestionType;
use App\Enums\SurveyStep;
use App\Services\CalorieCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalorieCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CalorieCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->service = new CalorieCalculationService();
    }

    /** @test */
    public function test_calculates_bmr_for_male_correctly()
    {
        // 남성: 88.362 + (13.397 × 70kg) + (4.799 × 175cm) - (5.677 × 30)
        // = 88.362 + 937.79 + 839.825 - 170.31 = 1695.67
        $bmr = $this->service->calculateBMR('male', 70, 175, 30);

        $this->assertEquals(1695.67, $bmr);
    }

    /** @test */
    public function test_calculates_bmr_for_female_correctly()
    {
        // 여성: 447.593 + (9.247 × 60kg) + (3.098 × 165cm) - (4.330 × 25)
        // = 447.593 + 554.82 + 511.17 - 108.25 = 1405.33
        $bmr = $this->service->calculateBMR('여성', 60, 165, 25);

        $this->assertEquals(1405.33, $bmr);
    }

    /** @test */
    public function test_calculates_tdee_with_activity_factor()
    {
        $bmr = 1500;

        // 좌식 생활
        $tdee = $this->service->calculateTDEE($bmr, 'sedentary');
        $this->assertEquals(1800.00, $tdee); // 1500 * 1.2

        // 보통 활동
        $tdee = $this->service->calculateTDEE($bmr, 'moderately_active');
        $this->assertEquals(2325.00, $tdee); // 1500 * 1.55
    }

    /** @test */
    public function test_calculates_target_calories_for_weight_loss()
    {
        $tdee = 2000;

        // 주 0.5kg 감량
        $target = $this->service->calculateTargetCalories($tdee, 'lose_0.5kg');
        $this->assertEquals(1500.00, $target); // 2000 - 500

        // 주 1kg 감량
        $target = $this->service->calculateTargetCalories($tdee, 'lose_1kg');
        $this->assertEquals(1200.00, $target); // 2000 - 1000, 최소 1200 보장
    }

    /** @test */
    public function test_ensures_minimum_calories()
    {
        $tdee = 1500;

        // 1000 감량 시도하지만 최소 1200 보장
        $target = $this->service->calculateTargetCalories($tdee, 'lose_1kg');
        $this->assertEquals(1200.00, $target);
    }

    /** @test */
    public function test_calculates_macros_correctly()
    {
        $targetCalories = 2000;

        $macros = $this->service->calculateMacros($targetCalories);

        // 탄수화물: 2000 * 0.4 / 4 = 200g
        $this->assertEquals(200.00, $macros['carbs_g']);

        // 단백질: 2000 * 0.3 / 4 = 150g
        $this->assertEquals(150.00, $macros['protein_g']);

        // 지방: 2000 * 0.3 / 9 = 66.67g
        $this->assertEquals(66.67, $macros['fat_g']);
    }

    /** @test */
    public function test_can_calculate_bmr_via_api()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/calculations/bmr', [
                'gender' => 'male',
                'weight' => 75,
                'height' => 180,
                'age' => 28,
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'bmr',
                    'unit',
                    'formula',
                ],
            ]);
    }

    /** @test */
    public function test_can_calculate_tdee_via_api()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/calculations/tdee', [
                'bmr' => 1700,
                'activity_level' => 'moderately_active',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'tdee' => 2635.00, // 1700 * 1.55
                    'bmr' => 1700,
                    'activity_level' => 'moderately_active',
                ],
            ]);
    }

    /** @test */
    public function test_requires_authentication()
    {
        $response = $this->postJson('/api/calculations/bmr', [
            'gender' => 'male',
            'weight' => 70,
            'height' => 175,
            'age' => 30,
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function test_validates_bmr_input()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/calculations/bmr', [
                'gender' => 'invalid',
                'weight' => 400, // Too high
                'height' => 50,  // Too low
                'age' => 150,    // Too high
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender', 'weight', 'height', 'age']);
    }

    /** @test */
    public function test_can_get_latest_calculation()
    {
        // Given: 계산 기록 생성
        $this->actingAs($this->user)
            ->postJson('/api/calculations/bmr', [
                'gender' => 'male',
                'weight' => 70,
                'height' => 175,
                'age' => 30,
            ]);

        // When: 최신 기록 조회
        $response = $this->actingAs($this->user)
            ->getJson('/api/calculations/latest');

        // Then: 404 (설문 기반 계산 아직 안 함)
        $response->assertNotFound();
    }
}
