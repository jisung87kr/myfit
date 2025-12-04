<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\ExerciseLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExerciseLog>
 */
class ExerciseLogFactory extends Factory
{
    protected $model = ExerciseLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $intensities = ['낮음', '보통', '높음', '매우 높음'];

        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'exercise_id' => Exercise::factory(),
            'exercise_name' => fake()->randomElement(['조깅', '걷기', '수영', '자전거', '요가', '필라테스', '웨이트 트레이닝']),
            'duration_minutes' => fake()->numberBetween(15, 120),
            'calories_burned' => fake()->randomFloat(2, 50, 600),
            'intensity' => fake()->randomElement($intensities),
            'exercise_time' => fake()->time('H:i'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Exercise log for today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Exercise log for a specific date
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Exercise log without exercise reference (manual entry)
     */
    public function manualEntry(): static
    {
        return $this->state(fn (array $attributes) => [
            'exercise_id' => null,
        ]);
    }

    /**
     * Low intensity exercise
     */
    public function lowIntensity(): static
    {
        return $this->state(fn (array $attributes) => [
            'intensity' => '낮음',
            'duration_minutes' => fake()->numberBetween(20, 60),
            'calories_burned' => fake()->randomFloat(2, 50, 200),
        ]);
    }

    /**
     * Medium intensity exercise
     */
    public function mediumIntensity(): static
    {
        return $this->state(fn (array $attributes) => [
            'intensity' => '보통',
            'duration_minutes' => fake()->numberBetween(30, 90),
            'calories_burned' => fake()->randomFloat(2, 150, 400),
        ]);
    }

    /**
     * High intensity exercise
     */
    public function highIntensity(): static
    {
        return $this->state(fn (array $attributes) => [
            'intensity' => '높음',
            'duration_minutes' => fake()->numberBetween(20, 60),
            'calories_burned' => fake()->randomFloat(2, 250, 500),
        ]);
    }

    /**
     * Very high intensity exercise
     */
    public function veryHighIntensity(): static
    {
        return $this->state(fn (array $attributes) => [
            'intensity' => '매우 높음',
            'duration_minutes' => fake()->numberBetween(15, 45),
            'calories_burned' => fake()->randomFloat(2, 300, 600),
        ]);
    }

    /**
     * Short duration exercise
     */
    public function shortDuration(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => fake()->numberBetween(10, 30),
        ]);
    }

    /**
     * Long duration exercise
     */
    public function longDuration(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => fake()->numberBetween(60, 120),
        ]);
    }
}
