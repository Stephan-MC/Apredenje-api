<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Admin::query()->where('email', 'superadmin@apredenje.test')->exists()) {
            Admin::factory()->superAdmin()->create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@apredenje.test',
            ]);
        }

        if (! Admin::query()->where('email', 'admin@apredenje.test')->exists()) {
            Admin::factory()->create([
                'first_name' => 'System',
                'last_name' => 'Admin',
                'email' => 'admin@apredenje.test',
            ]);
        }

        Admin::factory()->create();
    }
}
