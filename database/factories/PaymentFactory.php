<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'registrar_id' => Staff::factory(),
            'amount' => fake()->randomElement([100, 150, 200, 250, 350, 500]),
        ];
    }

    /**
     * Indicate that the payment has no assigned registrar.
     */
    public function withoutRegistrar(): static
    {
        return $this->state(fn (array $attributes): array => [
            'registrar_id' => null,
        ]);
    }
}
