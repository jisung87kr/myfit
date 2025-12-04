<?php

namespace Database\Factories;

use App\Models\DailyMealPlan;
use App\Models\DietPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyMealPlanFactory extends Factory
{
    protected $model = DailyMealPlan::class;

    public function definition(): array
    {
        return [
            'diet_plan_id' => DietPlan::factory(),
            'day_number' => $this->faker->numberBetween(1, 7),
            'date' => $this->faker->date(),
            'total_calories' => $this->faker->randomFloat(2, 1000, 2000),
            'total_protein_g' => $this->faker->randomFloat(2, 50, 150),
            'total_carbs_g' => $this->faker->randomFloat(2, 100, 300),
            'total_fat_g' => $this->faker->randomFloat(2, 30, 80),
            'tips' => $this->faker->optional()->sentence(),
        ];
    }
}
