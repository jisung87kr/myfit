<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_user_can_get_challenges_list(): void
    {
        Challenge::factory()->ongoing()->count(3)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/challenges');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'description', 'goal_type', 'goal_value']
                    ]
                ]
            ]);
    }

    public function test_can_filter_ongoing_challenges(): void
    {
        Challenge::factory()->ongoing()->count(2)->create();
        Challenge::factory()->upcoming()->count(1)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/challenges?status=ongoing');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_can_filter_upcoming_challenges(): void
    {
        Challenge::factory()->ongoing()->count(2)->create();
        Challenge::factory()->upcoming()->count(3)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/challenges?status=upcoming');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_user_can_view_challenge_details(): void
    {
        $challenge = Challenge::factory()->ongoing()->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/challenges/{$challenge->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.challenge.id', $challenge->id);
    }

    public function test_user_can_join_challenge(): void
    {
        $challenge = Challenge::factory()->ongoing()->create();

        $response = $this->withToken($this->token)
            ->postJson("/api/challenges/{$challenge->id}/join");

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('challenge_participants', [
            'challenge_id' => $challenge->id,
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);
    }

    public function test_joining_challenge_increments_participants_count(): void
    {
        $challenge = Challenge::factory()->ongoing()->create();

        $this->withToken($this->token)
            ->postJson("/api/challenges/{$challenge->id}/join");

        $this->assertEquals(1, $challenge->fresh()->participants_count);
    }

    public function test_user_cannot_join_challenge_twice(): void
    {
        $challenge = Challenge::factory()->ongoing()->create();

        $this->withToken($this->token)
            ->postJson("/api/challenges/{$challenge->id}/join");

        $response = $this->withToken($this->token)
            ->postJson("/api/challenges/{$challenge->id}/join");

        $response->assertStatus(400);
    }

    public function test_user_cannot_join_ended_challenge(): void
    {
        $challenge = Challenge::factory()->ended()->create();

        $response = $this->withToken($this->token)
            ->postJson("/api/challenges/{$challenge->id}/join");

        $response->assertStatus(400);
    }

    public function test_user_cannot_join_full_challenge(): void
    {
        $challenge = Challenge::factory()->ongoing()->create([
            'max_participants' => 1,
            'participants_count' => 1,
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/challenges/{$challenge->id}/join");

        $response->assertStatus(400);
    }

    public function test_user_can_leave_challenge(): void
    {
        $challenge = Challenge::factory()->ongoing()->create();

        $this->withToken($this->token)
            ->postJson("/api/challenges/{$challenge->id}/join");

        $response = $this->withToken($this->token)
            ->postJson("/api/challenges/{$challenge->id}/leave");

        $response->assertStatus(200);
        $this->assertDatabaseHas('challenge_participants', [
            'challenge_id' => $challenge->id,
            'user_id' => $this->user->id,
            'status' => 'withdrawn',
        ]);
    }

    public function test_user_can_get_own_challenges(): void
    {
        $challenge1 = Challenge::factory()->ongoing()->create();
        $challenge2 = Challenge::factory()->ongoing()->create();
        Challenge::factory()->ongoing()->create(); // Not joined

        ChallengeParticipant::factory()->create([
            'challenge_id' => $challenge1->id,
            'user_id' => $this->user->id,
        ]);
        ChallengeParticipant::factory()->create([
            'challenge_id' => $challenge2->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/challenges/my');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_can_get_challenge_leaderboard(): void
    {
        $challenge = Challenge::factory()->ongoing()->create();

        ChallengeParticipant::factory()->count(5)->create([
            'challenge_id' => $challenge->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/challenges/{$challenge->id}/leaderboard");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'progress', 'user']
                    ]
                ]
            ]);
    }
}
