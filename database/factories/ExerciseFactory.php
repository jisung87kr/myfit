<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        $categories = ['유산소', '근력', '스트레칭', '스포츠'];
        $intensities = ['낮음', '보통', '높음'];

        return [
            'name' => $this->faker->word() . ' 운동',
            'category' => $this->faker->randomElement($categories),
            'intensity' => $this->faker->randomElement($intensities),
            'met_value' => $this->faker->randomFloat(2, 1.5, 12.0),
            'calories_per_hour_per_kg' => $this->faker->randomFloat(2, 1.5, 12.0),
            'description' => $this->faker->sentence(),
            'video_url' => $this->faker->optional()->url(),
        ];
    }
}
