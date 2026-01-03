<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->post = Post::factory()->create();
    }

    public function test_user_can_get_comments_for_post(): void
    {
        Comment::factory()->count(3)->create(['post_id' => $this->post->id]);

        $response = $this->withToken($this->token)
            ->getJson("/api/posts/{$this->post->id}/comments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'content', 'user']
                    ]
                ]
            ]);
    }

    public function test_user_can_create_comment(): void
    {
        $response = $this->withToken($this->token)
            ->postJson("/api/posts/{$this->post->id}/comments", [
                'content' => '좋은 글이네요!',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.comment.content', '좋은 글이네요!');

        $this->assertDatabaseHas('comments', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'content' => '좋은 글이네요!',
        ]);
    }

    public function test_creating_comment_increments_post_comments_count(): void
    {
        $initialCount = $this->post->comments_count;

        $this->withToken($this->token)
            ->postJson("/api/posts/{$this->post->id}/comments", [
                'content' => 'Test comment',
            ]);

        $this->assertEquals($initialCount + 1, $this->post->fresh()->comments_count);
    }

    public function test_user_can_create_reply(): void
    {
        $parentComment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->withToken($this->token)
            ->postJson("/api/posts/{$this->post->id}/comments", [
                'content' => '대댓글입니다.',
                'parent_id' => $parentComment->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.comment.parent_id', $parentComment->id);
    }

    public function test_reply_must_be_on_same_post(): void
    {
        $otherPost = Post::factory()->create();
        $parentComment = Comment::factory()->create(['post_id' => $otherPost->id]);

        $response = $this->withToken($this->token)
            ->postJson("/api/posts/{$this->post->id}/comments", [
                'content' => '대댓글입니다.',
                'parent_id' => $parentComment->id,
            ]);

        $response->assertStatus(400);
    }

    public function test_user_can_update_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->token)
            ->putJson("/api/comments/{$comment->id}", [
                'content' => '수정된 댓글',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.comment.content', '수정된 댓글');
    }

    public function test_user_cannot_update_others_comment(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withToken($this->token)
            ->putJson("/api/comments/{$comment->id}", [
                'content' => '수정된 댓글',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_user_can_like_comment(): void
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->withToken($this->token)
            ->postJson("/api/comments/{$comment->id}/like");

        $response->assertStatus(200);
        $this->assertEquals(1, $comment->fresh()->likes_count);
    }

    public function test_user_can_unlike_comment(): void
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $this->withToken($this->token)
            ->postJson("/api/comments/{$comment->id}/like");

        $response = $this->withToken($this->token)
            ->deleteJson("/api/comments/{$comment->id}/like");

        $response->assertStatus(200);
        $this->assertEquals(0, $comment->fresh()->likes_count);
    }
}
