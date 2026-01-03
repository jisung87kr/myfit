<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeightLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->authToken = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function it_can_log_weight(): void
    {
        $date = now()->format('Y-m-d');

        $weightData = [
            'date' => $date,
            'weight' => 75.5,
            'notes' => '아침 측정',
        ];

        $response = $this->withToken($this->authToken)
            ->postJson('/api/weight-logs', $weightData);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'date',
                    'weight',
                    'notes',
                ],
            ])
            ->assertJson([
                'data' => [
                    'weight' => '75.50',
                    'notes' => '아침 측정',
                ],
            ]);

        $this->assertDatabaseHas('weight_logs', [
            'user_id' => $this->user->id,
            'weight' => 75.5,
        ]);
    }

    /** @test */
    public function it_calculates_weight_change_from_previous_entry(): void
    {
        // Create previous weight log
        WeightLog::factory()->for($this->user)->create([
            'date' => now()->subDay()->format('Y-m-d'),
            'weight' => 80.0,
        ]);

        $response = $this->withToken($this->authToken)
            ->postJson('/api/weight-logs', [
                'date' => now()->format('Y-m-d'),
                'weight' => 79.5,
            ]);

        $response->assertCreated()
            ->assertJsonFragment([
                'weight_change' => -0.5,
                'previous_weight' => '80.00',
            ]);
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/weight-logs', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'weight']);
    }

    /** @test */
    public function it_validates_weight_range(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/weight-logs', [
                'date' => now()->format('Y-m-d'),
                'weight' => 15, // Too low
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight']);

        $response = $this->withToken($this->authToken)
            ->postJson('/api/weight-logs', [
                'date' => now()->format('Y-m-d'),
                'weight' => 350, // Too high
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight']);
    }

    /** @test */
    public function it_prevents_duplicate_entries_for_same_date(): void
    {
        $date = now()->format('Y-m-d');

        WeightLog::factory()->for($this->user)->create([
            'date' => $date,
            'weight' => 75.0,
        ]);

        $response = $this->withToken($this->authToken)
            ->postJson('/api/weight-logs', [
                'date' => $date,
                'weight' => 76.0,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_can_get_weight_log_for_specific_date(): void
    {
        $date = now()->format('Y-m-d');

        WeightLog::factory()->for($this->user)->create([
            'date' => $date,
            'weight' => 75.5,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson("/api/weight-logs/show?date={$date}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'date' => $date,
                    'weight' => '75.50',
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_when_no_weight_log_for_date(): void
    {
        $date = now()->format('Y-m-d');

        $response = $this->withToken($this->authToken)
            ->getJson("/api/weight-logs/show?date={$date}");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_weight_log(): void
    {
        $weightLog = WeightLog::factory()->for($this->user)->create([
            'weight' => 75.0,
            'notes' => 'Original note',
        ]);

        $response = $this->withToken($this->authToken)
            ->putJson("/api/weight-logs/{$weightLog->id}", [
                'weight' => 74.5,
                'notes' => 'Updated note',
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'weight' => '74.50',
                    'notes' => 'Updated note',
                ],
            ]);

        $this->assertDatabaseHas('weight_logs', [
            'id' => $weightLog->id,
            'weight' => 74.5,
            'notes' => 'Updated note',
        ]);
    }

    /** @test */
    public function it_cannot_update_another_users_weight_log(): void
    {
        $otherUser = User::factory()->create();
        $weightLog = WeightLog::factory()->for($otherUser)->create();

        $response = $this->withToken($this->authToken)
            ->putJson("/api/weight-logs/{$weightLog->id}", [
                'weight' => 999,
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function it_can_delete_weight_log(): void
    {
        $weightLog = WeightLog::factory()->for($this->user)->create();

        $response = $this->withToken($this->authToken)
            ->deleteJson("/api/weight-logs/{$weightLog->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('weight_logs', [
            'id' => $weightLog->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_another_users_weight_log(): void
    {
        $otherUser = User::factory()->create();
        $weightLog = WeightLog::factory()->for($otherUser)->create();

        $response = $this->withToken($this->authToken)
            ->deleteJson("/api/weight-logs/{$weightLog->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('weight_logs', [
            'id' => $weightLog->id,
        ]);
    }

    /** @test */
    public function it_can_get_latest_weight(): void
    {
        WeightLog::factory()->for($this->user)->create([
            'date' => now()->subDays(5)->format('Y-m-d'),
            'weight' => 80.0,
        ]);

        WeightLog::factory()->for($this->user)->create([
            'date' => now()->subDays(2)->format('Y-m-d'),
            'weight' => 79.5,
        ]);

        $latestLog = WeightLog::factory()->for($this->user)->create([
            'date' => now()->format('Y-m-d'),
            'weight' => 79.0,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson('/api/weight-logs/latest');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $latestLog->id,
                    'weight' => '79.00',
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_when_no_weight_logs_exist(): void
    {
        $response = $this->withToken($this->authToken)
            ->getJson('/api/weight-logs/latest');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_get_weight_history(): void
    {
        // Create weight logs over 45 days
        for ($i = 0; $i < 45; $i++) {
            WeightLog::factory()->for($this->user)->create([
                'date' => now()->subDays($i)->format('Y-m-d'),
                'weight' => 80 - ($i * 0.1),
            ]);
        }

        // Request last 30 days
        $response = $this->withToken($this->authToken)
            ->getJson('/api/weight-logs/history?days=30');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'period_days',
                    'entry_count',
                    'weights',
                ],
            ])
            ->assertJson([
                'data' => [
                    'period_days' => 30,
                    'entry_count' => 30,
                ],
            ]);
    }

    /** @test */
    public function it_can_get_weight_progress(): void
    {
        // Create weight progression (losing weight)
        WeightLog::factory()->for($this->user)->create([
            'date' => now()->subDays(30)->format('Y-m-d'),
            'weight' => 85.0,
        ]);

        WeightLog::factory()->for($this->user)->create([
            'date' => now()->subDays(15)->format('Y-m-d'),
            'weight' => 82.5,
        ]);

        WeightLog::factory()->for($this->user)->create([
            'date' => now()->format('Y-m-d'),
            'weight' => 80.0,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson('/api/weight-logs/progress');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'has_data',
                    'start_date',
                    'start_weight',
                    'latest_date',
                    'latest_weight',
                    'weight_change',
                    'percentage_change',
                    'total_days',
                    'total_entries',
                ],
            ])
            ->assertJson([
                'data' => [
                    'has_data' => true,
                    'start_weight' => '85.00',
                    'latest_weight' => '80.00',
                    'weight_change' => -5.0,
                    'total_entries' => 3,
                ],
            ]);
    }

    /** @test */
    public function it_can_get_statistics_for_date_range(): void
    {
        $startDate = now()->subDays(30)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        // Create various weight entries
        WeightLog::factory()->for($this->user)->create([
            'date' => now()->subDays(30)->format('Y-m-d'),
            'weight' => 82.0,
        ]);

        WeightLog::factory()->for($this->user)->create([
            'date' => now()->subDays(20)->format('Y-m-d'),
            'weight' => 85.0, // Max
        ]);

        WeightLog::factory()->for($this->user)->create([
            'date' => now()->subDays(10)->format('Y-m-d'),
            'weight' => 78.0, // Min
        ]);

        WeightLog::factory()->for($this->user)->create([
            'date' => now()->format('Y-m-d'),
            'weight' => 80.0,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson("/api/weight-logs/statistics?start_date={$startDate}&end_date={$endDate}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'has_data',
                    'period',
                    'min_weight',
                    'max_weight',
                    'avg_weight',
                    'current_weight',
                    'entry_count',
                ],
            ])
            ->assertJson([
                'data' => [
                    'has_data' => true,
                    'min_weight' => '78.00',
                    'max_weight' => '85.00',
                    'avg_weight' => 81.25,
                    'current_weight' => '80.00',
                    'entry_count' => 4,
                ],
            ]);
    }

    /** @test */
    public function it_validates_statistics_date_range(): void
    {
        $response = $this->withToken($this->authToken)
            ->getJson('/api/weight-logs/statistics?start_date=2025-12-10&end_date=2025-12-01');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints(): void
    {
        $date = now()->format('Y-m-d');

        // Show
        $this->getJson("/api/weight-logs/show?date={$date}")
            ->assertUnauthorized();

        // Store
        $this->postJson('/api/weight-logs', [])
            ->assertUnauthorized();

        // Update
        $this->putJson('/api/weight-logs/1', [])
            ->assertUnauthorized();

        // Delete
        $this->deleteJson('/api/weight-logs/1')
            ->assertUnauthorized();

        // Latest
        $this->getJson('/api/weight-logs/latest')
            ->assertUnauthorized();

        // History
        $this->getJson('/api/weight-logs/history')
            ->assertUnauthorized();

        // Progress
        $this->getJson('/api/weight-logs/progress')
            ->assertUnauthorized();

        // Statistics
        $this->getJson("/api/weight-logs/statistics?start_date={$date}&end_date={$date}")
            ->assertUnauthorized();
    }

    /** @test */
    public function it_only_shows_users_own_weight_logs(): void
    {
        $otherUser = User::factory()->create();

        // Create weight logs for both users
        WeightLog::factory()->for($this->user)->create([
            'date' => now()->format('Y-m-d'),
            'weight' => 75.0,
        ]);

        WeightLog::factory()->for($otherUser)->create([
            'date' => now()->format('Y-m-d'),
            'weight' => 80.0,
        ]);

        $response = $this->withToken($this->authToken)
            ->getJson('/api/weight-logs/latest');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'weight' => '75.00',
                ],
            ]);
    }

    /** @test */
    public function it_handles_decimal_weights_correctly(): void
    {
        $response = $this->withToken($this->authToken)
            ->postJson('/api/weight-logs', [
                'date' => now()->format('Y-m-d'),
                'weight' => 75.75,
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'weight' => '75.75',
                ],
            ]);

        $this->assertDatabaseHas('weight_logs', [
            'user_id' => $this->user->id,
            'weight' => 75.75,
        ]);
    }
}
