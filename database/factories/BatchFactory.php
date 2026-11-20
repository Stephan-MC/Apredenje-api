<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\BatchType;
use App\Models\Session;
use App\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_type_id' => BatchType::factory(),
            'session_id' => Session::factory(),
            'trainer_id' => Trainer::factory(),
        ];
    }
}
