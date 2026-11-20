<?php

namespace Database\Factories;

use App\Models\Trainer;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Training>
 */
class TrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'code' => strtoupper(fake()->unique()->bothify('TRN-###')),
            'trainer_id' => Trainer::factory(),
            'fee' => fake()->numberBetween(100, 1000),
        ];
    }

    /**
     * Indicate that the training has no assigned trainer.
     */
    public function withoutTrainer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'trainer_id' => null,
        ]);
    }
}
