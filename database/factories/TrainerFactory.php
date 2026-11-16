<?php

namespace Database\Factories;

use App\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trainer>
 */
class TrainerFactory extends UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Trainer>
     */
    protected $model = Trainer::class;
}
