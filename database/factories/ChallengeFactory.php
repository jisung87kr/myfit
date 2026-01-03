<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 week', '+1 week');
        $endDate = (clone $startDate)->modify('+30 days');

        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'image_url' => null,
            'goal_type' => fake()->randomElement(['weight_loss', 'exercise_count', 'streak', 'meal_log']),
            'goal_value' => fake()->randomFloat(1, 5, 30),
            'goal_unit' => fake()->randomElement(['kg', '회', '일']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'participants_count' => 0,
            'max_participants' => null,
            'is_active' => true,
            'badge_id' => null,
        ];
    }

    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
            'is_active' => true,
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(35),
            'is_active' => true,
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(35),
            'end_date' => now()->subDays(5),
        ]);
    }

    public function weightLoss(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_type' => 'weight_loss',
            'goal_value' => 5,
            'goal_unit' => 'kg',
        ]);
    }

    public function exerciseCount(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_type' => 'exercise_count',
            'goal_value' => 30,
            'goal_unit' => '회',
        ]);
    }
}
