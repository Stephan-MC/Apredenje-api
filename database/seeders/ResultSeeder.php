<?php

namespace Database\Seeders;

use App\Models\Result;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::query()->with('training.quizzes')->get();
        $created = 0;

        foreach ($students as $student) {
            foreach ($student->training?->quizzes ?? collect() as $quiz) {
                Result::factory()->create([
                    'trainee_id' => $student->trainee_id,
                    'quiz_id' => $quiz->id,
                    'mark' => fake()->numberBetween(0, $quiz->mark),
                ]);
                $created++;
            }
        }

        if ($created === 0) {
            Result::factory()->count(5)->create();
        }
    }
}
