<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['user:id,name,profile_photo_path'])
            ->withCount(['comments', 'likes']);

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderByDesc('likes_count');
                break;
            case 'views':
                $query->orderByDesc('views_count');
                break;
            default:
                $query->orderByDesc('is_pinned')->orderByDesc('created_at');
        }

        $posts = $query->paginate($request->get('per_page', 15));

        // Add is_liked for authenticated user
        if (auth()->check()) {
            $userId = auth()->id();
            $posts->getCollection()->transform(function ($post) use ($userId) {
                $post->is_liked = $post->likes()->where('user_id', $userId)->exists();
                return $post;
            });
        }

        return response()->success('Posts retrieved successfully', $posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|in:success_story,tip,question,daily',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'images' => 'array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $imageUrls = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public');
                $imageUrls[] = Storage::url($path);
            }
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'category' => $request->category,
            'title' => $request->title,
            'content' => $request->content,
            'images' => $imageUrls ?: null,
        ]);

        $post->load('user:id,name,profile_photo_path');

        return response()->created('Post created successfully', ['post' => $post]);
    }

    public function show(Post $post): JsonResponse
    {
        $post->incrementViews();
        $post->load(['user:id,name,profile_photo_path', 'rootComments.user:id,name,profile_photo_path', 'rootComments.replies.user:id,name,profile_photo_path']);
        $post->loadCount(['comments', 'likes']);

        if (auth()->check()) {
            $post->is_liked = $post->isLikedBy(auth()->user());
        }

        return response()->success('Post retrieved successfully', ['post' => $post]);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        if ($post->user_id !== auth()->id()) {
            return response()->forbidden('You are not authorized to update this post');
        }

        $validator = Validator::make($request->all(), [
            'category' => 'in:success_story,tip,question,daily',
            'title' => 'string|max:255',
            'content' => 'string|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->validationError($validator->errors());
        }

        $post->update($request->only(['category', 'title', 'content']));

        return response()->success('Post updated successfully', ['post' => $post->fresh()]);
    }

    public function destroy(Post $post): JsonResponse
    {
        if ($post->user_id !== auth()->id()) {
            return response()->forbidden('You are not authorized to delete this post');
        }

        $post->delete();

        return response()->success('Post deleted successfully');
    }

    public function like(Post $post): JsonResponse
    {
        $userId = auth()->id();

        $existingLike = Like::where('user_id', $userId)
            ->where('likeable_type', Post::class)
            ->where('likeable_id', $post->id)
            ->first();

        if ($existingLike) {
            return response()->error('You already liked this post', [], 400);
        }

        Like::create([
            'user_id' => $userId,
            'likeable_type' => Post::class,
            'likeable_id' => $post->id,
        ]);

        return response()->success('Post liked successfully', [
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function unlike(Post $post): JsonResponse
    {
        $deleted = Like::where('user_id', auth()->id())
            ->where('likeable_type', Post::class)
            ->where('likeable_id', $post->id)
            ->delete();

        if (!$deleted) {
            return response()->error('You have not liked this post', [], 400);
        }

        return response()->success('Post unliked successfully', [
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function myPosts(Request $request): JsonResponse
    {
        $posts = Post::where('user_id', auth()->id())
            ->withCount(['comments', 'likes'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->success('My posts retrieved successfully', $posts);
    }

    public function categories(): JsonResponse
    {
        $categories = [
            ['value' => 'success_story', 'label' => '성공 스토리', 'description' => '다이어트 성공 경험을 공유해주세요'],
            ['value' => 'tip', 'label' => '꿀팁', 'description' => '유용한 다이어트 팁을 공유해주세요'],
            ['value' => 'question', 'label' => '질문', 'description' => '궁금한 것을 물어보세요'],
            ['value' => 'daily', 'label' => '일상', 'description' => '오늘 하루를 공유해주세요'],
        ];

        return response()->success('Categories retrieved successfully', ['categories' => $categories]);
    }
}
