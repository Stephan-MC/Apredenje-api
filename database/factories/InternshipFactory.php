<?php

namespace Database\Factories;

use App\Models\Internship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Internship>
 */
class InternshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'start_time' => fake()->dateTimeBetween('now', '+3 months'),
            'duration' => fake()->randomElement([30, 60, 90, 120, 180]),
        ];
    }
}
