<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeightLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeightLog>
 */
class WeightLogFactory extends Factory
{
    protected $model = WeightLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            'weight' => fake()->randomFloat(2, 50, 120),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Weight log for today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Weight log for a specific date
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Weight log with specific weight
     */
    public function withWeight(float $weight): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $weight,
        ]);
    }

    /**
     * Lightweight weight log
     */
    public function lightweight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => fake()->randomFloat(2, 50, 65),
        ]);
    }

    /**
     * Heavyweight weight log
     */
    public function heavyweight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => fake()->randomFloat(2, 85, 120),
        ]);
    }

    /**
     * Create a weight progression (losing weight)
     */
    public function losingWeight(User $user, int $days = 30, float $startWeight = 80, float $totalLoss = 5): array
    {
        $logs = [];
        $dailyLoss = $totalLoss / $days;

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($days - $i - 1)->format('Y-m-d');
            $weight = $startWeight - ($dailyLoss * $i) + fake()->randomFloat(2, -0.3, 0.3); // Add some variance

            $logs[] = WeightLog::factory()->for($user)->create([
                'date' => $date,
                'weight' => round($weight, 2),
            ]);
        }

        return $logs;
    }

    /**
     * Create a weight progression (gaining weight)
     */
    public function gainingWeight(User $user, int $days = 30, float $startWeight = 60, float $totalGain = 3): array
    {
        $logs = [];
        $dailyGain = $totalGain / $days;

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($days - $i - 1)->format('Y-m-d');
            $weight = $startWeight + ($dailyGain * $i) + fake()->randomFloat(2, -0.2, 0.2); // Add some variance

            $logs[] = WeightLog::factory()->for($user)->create([
                'date' => $date,
                'weight' => round($weight, 2),
            ]);
        }

        return $logs;
    }
}
