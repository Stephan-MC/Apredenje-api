<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Training;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
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
            Quiz::factory()->count(2)->for($training)->create();
        }
    }
}
