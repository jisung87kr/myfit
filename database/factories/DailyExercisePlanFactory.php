<?php

namespace Database\Factories;

use App\Models\DailyExercisePlan;
use App\Models\DietPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyExercisePlanFactory extends Factory
{
    protected $model = DailyExercisePlan::class;

    public function definition(): array
    {
        $intensities = ['낮음', '보통', '높음'];

        return [
            'diet_plan_id' => DietPlan::factory(),
            'day_number' => $this->faker->numberBetween(1, 7),
            'date' => $this->faker->date(),
            'exercise_id' => null,
            'exercise_name' => $this->faker->word() . ' 운동',
            'duration_minutes' => $this->faker->numberBetween(20, 60),
            'estimated_calories_burned' => $this->faker->randomFloat(2, 100, 500),
            'intensity' => $this->faker->randomElement($intensities),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
