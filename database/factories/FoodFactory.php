<?php

namespace Database\Factories;

use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;

class FoodFactory extends Factory
{
    protected $model = Food::class;

    public function definition(): array
    {
        $categories = ['곡류', '단백질', '채소', '과일', '유제품', '견과류', '음료', '기타'];

        return [
            'name' => $this->faker->word() . ' 음식',
            'name_en' => $this->faker->word() . ' Food',
            'category' => $this->faker->randomElement($categories),
            'serving_size' => $this->faker->randomFloat(2, 50, 300),
            'calories' => $this->faker->randomFloat(2, 10, 500),
            'protein_g' => $this->faker->randomFloat(2, 0, 50),
            'carbs_g' => $this->faker->randomFloat(2, 0, 80),
            'fat_g' => $this->faker->randomFloat(2, 0, 40),
            'fiber_g' => $this->faker->randomFloat(2, 0, 20),
            'sodium_mg' => $this->faker->randomFloat(2, 0, 1000),
            'image_url' => $this->faker->optional()->imageUrl(),
        ];
    }
}
