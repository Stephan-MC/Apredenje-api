<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin;

/**
 * @extends Factory<\App\Models\Admin>
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

    /**
     * Indicate that the admin should also be assigned the super-admin role.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (Admin $admin): void {
            $admin->assign('admin');
        });
    }
}
