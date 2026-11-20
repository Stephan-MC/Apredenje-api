<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Result;
use App\Models\Trainee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Result>
 */
class ResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trainee_id' => Trainee::factory(),
            'quiz_id' => Quiz::factory(),
            'mark' => fake()->numberBetween(0, 100),
        ];
    }
}
