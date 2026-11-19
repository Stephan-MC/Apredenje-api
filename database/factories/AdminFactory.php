<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends StaffFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Admin>
     */
    protected $model = Admin::class;

    /**
     * Indicate that the admin should also be assigned the super-admin role.
     */
    public function superAdmin(): static
    {
        return $this->afterCreating(function (Admin $admin): void {
            $admin->assign('super-admin');
        });
    }
}
