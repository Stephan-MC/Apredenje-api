<?php

namespace Database\Factories;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Staff>
     */
    protected $model = Staff::class;
}
