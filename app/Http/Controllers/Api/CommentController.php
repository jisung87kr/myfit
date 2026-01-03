<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function index(Post $post, Request $request): JsonResponse
    {
        $comments = $post->rootComments()
            ->with(['user:id,name,profile_photo_path', 'replies.user:id,name,profile_photo_path'])
            ->withCount('likes')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        if (auth()->check()) {
            $userId = auth()->id();
            $comments->getCollection()->transform(function ($comment) use ($userId) {
                $comment->is_liked = $comment->likes()->where('user_id', $userId)->exists();
                $comment->replies->transform(function ($reply) use ($userId) {
                    $reply->is_liked = $reply->likes()->where('user_id', $userId)->exists();
                    return $reply;
                });
                return $comment;
            });
        }

        return response()->success('Comments retrieved successfully', $comments);
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        // Validate parent comment belongs to the same post
        if ($request->parent_id) {
            $parent = Comment::find($request->parent_id);
            if ($parent->post_id !== $post->id) {
                return response()->error('Parent comment does not belong to this post', [], 400);
            }
        }

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        $comment->load('user:id,name,profile_photo_path');

        return response()->created('Comment created successfully', ['comment' => $comment]);
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== auth()->id()) {
            return response()->forbidden('You are not authorized to update this comment');
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $comment->update(['content' => $request->content]);

        return response()->success('Comment updated successfully', ['comment' => $comment->fresh()]);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        if ($comment->user_id !== auth()->id()) {
            return response()->forbidden('You are not authorized to delete this comment');
        }

        $comment->delete();

        return response()->success('Comment deleted successfully');
    }

    public function like(Comment $comment): JsonResponse
    {
        $userId = auth()->id();

        $existingLike = Like::where('user_id', $userId)
            ->where('likeable_type', Comment::class)
            ->where('likeable_id', $comment->id)
            ->first();

        if ($existingLike) {
            return response()->error('You already liked this comment', [], 400);
        }

        Like::create([
            'user_id' => $userId,
            'likeable_type' => Comment::class,
            'likeable_id' => $comment->id,
        ]);

        return response()->success('Comment liked successfully', [
            'likes_count' => $comment->fresh()->likes_count,
        ]);
    }

    public function unlike(Comment $comment): JsonResponse
    {
        $deleted = Like::where('user_id', auth()->id())
            ->where('likeable_type', Comment::class)
            ->where('likeable_id', $comment->id)
            ->delete();

        if (!$deleted) {
            return response()->error('You have not liked this comment', [], 400);
        }

        return response()->success('Comment unliked successfully', [
            'likes_count' => $comment->fresh()->likes_count,
        ]);
    }
}
