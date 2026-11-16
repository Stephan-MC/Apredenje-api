<?php

namespace Database\Factories;

use App\Models\Trainee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trainee>
 */
class TraineeFactory extends UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Trainee>
     */
    protected $model = Trainee::class;
}
