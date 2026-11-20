<?php

namespace Database\Seeders;

use App\Models\Internship;
use App\Models\User;
use Illuminate\Database\Seeder;

class InternshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            Internship::factory()->count(5)->create();

            return;
        }

        foreach ($users as $user) {
            Internship::factory()->for($user)->create();
        }
    }
}
