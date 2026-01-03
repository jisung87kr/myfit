<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
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

    public function test_user_can_get_posts_list(): void
    {
        Post::factory()->count(5)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'title', 'content', 'category', 'user']
                    ]
                ]
            ]);
    }

    public function test_user_can_filter_posts_by_category(): void
    {
        Post::factory()->create(['category' => 'tip']);
        Post::factory()->create(['category' => 'question']);

        $response = $this->withToken($this->token)
            ->getJson('/api/posts?category=tip');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('tip', $data[0]['category']);
    }

    public function test_user_can_create_post(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/posts', [
                'category' => 'success_story',
                'title' => '10kg 감량 성공!',
                'content' => '3개월 동안 열심히 노력해서 10kg을 감량했습니다.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.post.title', '10kg 감량 성공!');

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'title' => '10kg 감량 성공!',
        ]);
    }

    public function test_post_creation_requires_valid_category(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/posts', [
                'category' => 'invalid_category',
                'title' => 'Test Title',
                'content' => 'Test content',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_user_can_view_single_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.post.id', $post->id);
    }

    public function test_viewing_post_increments_view_count(): void
    {
        $post = Post::factory()->create(['views_count' => 0]);

        $this->withToken($this->token)
            ->getJson("/api/posts/{$post->id}");

        $this->assertEquals(1, $post->fresh()->views_count);
    }

    public function test_user_can_update_own_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->putJson("/api/posts/{$post->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.post.title', 'Updated Title');
    }

    public function test_user_cannot_update_others_post(): void
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withToken($this->token)
            ->putJson("/api/posts/{$post->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_others_post(): void
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_like_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->withToken($this->token)
            ->postJson("/api/posts/{$post->id}/like");

        $response->assertStatus(200);
        $this->assertEquals(1, $post->fresh()->likes_count);
    }

    public function test_user_cannot_like_post_twice(): void
    {
        $post = Post::factory()->create();

        $this->withToken($this->token)
            ->postJson("/api/posts/{$post->id}/like");

        $response = $this->withToken($this->token)
            ->postJson("/api/posts/{$post->id}/like");

        $response->assertStatus(400);
    }

    public function test_user_can_unlike_post(): void
    {
        $post = Post::factory()->create();

        $this->withToken($this->token)
            ->postJson("/api/posts/{$post->id}/like");

        $response = $this->withToken($this->token)
            ->deleteJson("/api/posts/{$post->id}/like");

        $response->assertStatus(200);
        $this->assertEquals(0, $post->fresh()->likes_count);
    }

    public function test_user_can_get_own_posts(): void
    {
        Post::factory()->count(3)->create(['user_id' => $this->user->id]);
        Post::factory()->count(2)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/posts/my');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_can_get_post_categories(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/posts/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'categories' => [
                        '*' => ['value', 'label', 'description']
                    ]
                ]
            ]);
    }
}
