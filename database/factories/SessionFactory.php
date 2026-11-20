<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_id' => Training::factory(),
            'time' => fake()->dateTimeBetween('now', '+2 months'),
            'duration' => fake()->randomElement([60, 90, 120, 180]),
        ];
    }
}
