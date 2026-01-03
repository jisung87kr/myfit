<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => fake()->randomElement(['success_story', 'tip', 'question', 'daily']),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'images' => null,
            'likes_count' => 0,
            'comments_count' => 0,
            'views_count' => 0,
            'is_pinned' => false,
        ];
    }

    public function successStory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'success_story',
        ]);
    }

    public function tip(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'tip',
        ]);
    }

    public function question(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'question',
        ]);
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }
}
