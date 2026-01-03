<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'challenge_id' => Challenge::factory(),
            'user_id' => User::factory(),
            'status' => 'active',
            'progress' => 0,
            'current_value' => 0,
            'joined_at' => now(),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    public function withProgress(float $progress): static
    {
        return $this->state(fn (array $attributes) => [
            'progress' => $progress,
        ]);
    }
}
