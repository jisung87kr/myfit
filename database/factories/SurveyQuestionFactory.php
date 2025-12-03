<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Enums\SurveyStep;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    protected $model = SurveyQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questionType = fake()->randomElement(QuestionType::cases());

        return [
            'survey_id' => Survey::factory(),
            'step' => fake()->randomElement(SurveyStep::cases()),
            'question_text' => fake()->sentence() . '?',
            'question_type' => $questionType,
            'options' => $questionType->requiresOptions()
                ? ['옵션1', '옵션2', '옵션3', '옵션4']
                : null,
            'is_required' => fake()->boolean(80), // 80% required
            'order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Text type question.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionType::TEXT,
            'options' => null,
        ]);
    }

    /**
     * Number type question.
     */
    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionType::NUMBER,
            'options' => null,
        ]);
    }

    /**
     * Select type question.
     */
    public function select(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionType::SELECT,
            'options' => ['옵션1', '옵션2', '옵션3'],
        ]);
    }

    /**
     * Multi-select type question.
     */
    public function multiSelect(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionType::MULTI_SELECT,
            'options' => ['A', 'B', 'C', 'D', 'E'],
        ]);
    }

    /**
     * Required question.
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    /**
     * Optional question.
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }

    /**
     * For a specific step.
     */
    public function forStep(SurveyStep $step): static
    {
        return $this->state(fn (array $attributes) => [
            'step' => $step,
        ]);
    }
}
