<?php

namespace Database\Factories;

use App\Models\Intern;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Intern>
 */
class InternFactory extends UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Intern>
     */
    protected $model = Intern::class;
}
