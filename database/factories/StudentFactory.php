<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Student;
use App\Models\Trainee;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
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
            'training_id' => Training::factory(),
            'registrar_id' => Staff::factory(),
            'fee' => fake()->randomElement([150, 250, 350, 500]),
            'fee_reason' => fake()->optional(0.3)->randomElement([
                'Early bird discount',
                'Scholarship reduction',
                'Group enrollment rate',
            ]),
        ];
    }

    /**
     * Indicate that the student registration has no assigned registrar.
     */
    public function withoutRegistrar(): static
    {
        return $this->state(fn (array $attributes): array => [
            'registrar_id' => null,
        ]);
    }
}
