<?php

namespace Database\Seeders;

use App\Models\Session;
use App\Models\Training;
use Illuminate\Database\Seeder;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainings = Training::query()->get();

        if ($trainings->isEmpty()) {
            $trainings = Training::factory()->count(3)->create();
        }

        foreach ($trainings as $training) {
            Session::factory()->count(2)->for($training)->create();
        }
    }
}
