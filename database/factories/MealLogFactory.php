<?php

namespace Database\Factories;

use App\Models\Food;
use App\Models\MealLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MealLog>
 */
class MealLogFactory extends Factory
{
    protected $model = MealLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'meal_type' => fake()->randomElement($mealTypes),
            'food_id' => Food::factory(),
            'food_name' => fake()->words(2, true),
            'serving_size' => fake()->randomFloat(2, 50, 300),
            'calories' => fake()->randomFloat(2, 100, 800),
            'protein_g' => fake()->randomFloat(2, 5, 50),
            'carbs_g' => fake()->randomFloat(2, 10, 100),
            'fat_g' => fake()->randomFloat(2, 2, 40),
            'meal_time' => fake()->time('H:i'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Meal log for breakfast
     */
    public function breakfast(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => 'breakfast',
            'meal_time' => fake()->time('H:i', '10:00'),
        ]);
    }

    /**
     * Meal log for lunch
     */
    public function lunch(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => 'lunch',
            'meal_time' => fake()->time('H:i', '15:00'),
        ]);
    }

    /**
     * Meal log for dinner
     */
    public function dinner(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => 'dinner',
            'meal_time' => fake()->time('H:i', '21:00'),
        ]);
    }

    /**
     * Meal log for snack
     */
    public function snack(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => 'snack',
            'meal_time' => fake()->optional()->time('H:i'),
        ]);
    }

    /**
     * Meal log for today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Meal log for a specific date
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Meal log without food reference (manual entry)
     */
    public function manualEntry(): static
    {
        return $this->state(fn (array $attributes) => [
            'food_id' => null,
        ]);
    }

    /**
     * Meal log with high protein
     */
    public function highProtein(): static
    {
        return $this->state(fn (array $attributes) => [
            'protein_g' => fake()->randomFloat(2, 30, 60),
            'calories' => fake()->randomFloat(2, 300, 600),
        ]);
    }

    /**
     * Meal log with low calories
     */
    public function lowCalorie(): static
    {
        return $this->state(fn (array $attributes) => [
            'calories' => fake()->randomFloat(2, 50, 200),
            'protein_g' => fake()->randomFloat(2, 3, 15),
            'carbs_g' => fake()->randomFloat(2, 5, 30),
            'fat_g' => fake()->randomFloat(2, 1, 10),
        ]);
    }
}
