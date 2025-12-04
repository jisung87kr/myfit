<?php

namespace Database\Factories;

use App\Models\DietPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DietPlanFactory extends Factory
{
    protected $model = DietPlan::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');

        return [
            'user_id' => User::factory(),
            'survey_response_id' => null,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->modify('+6 days'),
            'target_calories_per_day' => $this->faker->numberBetween(1200, 2500),
            'ai_summary' => $this->faker->sentence(),
            'generation_prompt' => $this->faker->paragraph(),
        ];
    }

    /**
     * Indicate that the plan is generating.
     */
    public function generating(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'generating',
        ]);
    }

    /**
     * Indicate that the plan is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the plan is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the plan is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
