<?php

namespace Database\Factories;

use App\Models\DailyMealPlan;
use App\Models\MealPlanItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MealPlanItemFactory extends Factory
{
    protected $model = MealPlanItem::class;

    public function definition(): array
    {
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

        return [
            'daily_meal_plan_id' => DailyMealPlan::factory(),
            'meal_type' => $this->faker->randomElement($mealTypes),
            'food_id' => null,
            'food_name' => $this->faker->word() . ' 음식',
            'serving_size' => $this->faker->randomFloat(2, 50, 300),
            'calories' => $this->faker->randomFloat(2, 50, 500),
            'protein_g' => $this->faker->randomFloat(2, 5, 50),
            'carbs_g' => $this->faker->randomFloat(2, 10, 80),
            'fat_g' => $this->faker->randomFloat(2, 2, 40),
            'order' => 0,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that this is a breakfast item.
     */
    public function breakfast(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => 'breakfast',
        ]);
    }

    /**
     * Indicate that this is a lunch item.
     */
    public function lunch(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => 'lunch',
        ]);
    }

    /**
     * Indicate that this is a dinner item.
     */
    public function dinner(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => 'dinner',
        ]);
    }

    /**
     * Indicate that this is a snack item.
     */
    public function snack(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => 'snack',
        ]);
    }
}
