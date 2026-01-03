<?php

namespace Tests\Feature;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FriendshipTest extends TestCase
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

    public function test_user_can_get_friends_list(): void
    {
        $friend = User::factory()->create();
        Friendship::factory()->accepted()->create([
            'user_id' => $this->user->id,
            'friend_id' => $friend->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/friends');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.friends');
    }

    public function test_user_can_send_friend_request(): void
    {
        $friend = User::factory()->create();

        $response = $this->withToken($this->token)
            ->postJson('/api/friends/request', [
                'friend_id' => $friend->id,
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('friendships', [
            'user_id' => $this->user->id,
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_send_friend_request_to_self(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/friends/request', [
                'friend_id' => $this->user->id,
            ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_send_duplicate_friend_request(): void
    {
        $friend = User::factory()->create();

        $this->withToken($this->token)
            ->postJson('/api/friends/request', [
                'friend_id' => $friend->id,
            ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/friends/request', [
                'friend_id' => $friend->id,
            ]);

        $response->assertStatus(400);
    }

    public function test_user_can_accept_friend_request(): void
    {
        $sender = User::factory()->create();
        $friendship = Friendship::factory()->create([
            'user_id' => $sender->id,
            'friend_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/friends/{$friendship->id}/accept");

        $response->assertStatus(200);
        $this->assertEquals('accepted', $friendship->fresh()->status);
    }

    public function test_user_can_reject_friend_request(): void
    {
        $sender = User::factory()->create();
        $friendship = Friendship::factory()->create([
            'user_id' => $sender->id,
            'friend_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/friends/{$friendship->id}/reject");

        $response->assertStatus(200);
        $this->assertEquals('rejected', $friendship->fresh()->status);
    }

    public function test_user_cannot_accept_request_sent_to_others(): void
    {
        $otherUser = User::factory()->create();
        $sender = User::factory()->create();
        $friendship = Friendship::factory()->create([
            'user_id' => $sender->id,
            'friend_id' => $otherUser->id,
            'status' => 'pending',
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/friends/{$friendship->id}/accept");

        $response->assertStatus(403);
    }

    public function test_user_can_cancel_sent_request(): void
    {
        $friend = User::factory()->create();
        $friendship = Friendship::factory()->create([
            'user_id' => $this->user->id,
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/friends/{$friendship->id}/cancel");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('friendships', ['id' => $friendship->id]);
    }

    public function test_user_can_remove_friend(): void
    {
        $friend = User::factory()->create();
        Friendship::factory()->accepted()->create([
            'user_id' => $this->user->id,
            'friend_id' => $friend->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/friends/{$friend->id}");

        $response->assertStatus(200);
    }

    public function test_user_can_get_pending_requests(): void
    {
        $sender1 = User::factory()->create();
        $sender2 = User::factory()->create();

        Friendship::factory()->create([
            'user_id' => $sender1->id,
            'friend_id' => $this->user->id,
            'status' => 'pending',
        ]);
        Friendship::factory()->create([
            'user_id' => $sender2->id,
            'friend_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/friends/pending');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.requests');
    }

    public function test_user_can_get_sent_requests(): void
    {
        $friend = User::factory()->create();
        Friendship::factory()->create([
            'user_id' => $this->user->id,
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/friends/sent');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.requests');
    }

    public function test_user_can_block_another_user(): void
    {
        $userToBlock = User::factory()->create();

        $response = $this->withToken($this->token)
            ->postJson("/api/friends/{$userToBlock->id}/block");

        $response->assertStatus(200);
        $this->assertDatabaseHas('friendships', [
            'user_id' => $this->user->id,
            'friend_id' => $userToBlock->id,
            'status' => 'blocked',
        ]);
    }

    public function test_user_can_unblock_user(): void
    {
        $blockedUser = User::factory()->create();
        Friendship::factory()->blocked()->create([
            'user_id' => $this->user->id,
            'friend_id' => $blockedUser->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/friends/{$blockedUser->id}/block");

        $response->assertStatus(200);
    }

    public function test_user_can_search_users(): void
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Doe']);
        User::factory()->create(['name' => 'Bob Smith']);

        $response = $this->withToken($this->token)
            ->getJson('/api/friends/search?q=Doe');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.users');
    }
}
