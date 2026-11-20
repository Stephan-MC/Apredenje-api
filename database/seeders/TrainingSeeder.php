<?php

namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\Training;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainers = Trainer::query()->get();

        if ($trainers->isEmpty()) {
            $trainers = Trainer::factory()->count(3)->create();
        }

        $trainers = $trainers->values();

        $courses = [
            ['title' => 'Laravel Backend Development', 'code' => 'LAR-101', 'fee' => 500],
            ['title' => 'Modern Frontend with Vue & TypeScript', 'code' => 'VUE-102', 'fee' => 450],
            ['title' => 'Database Design & SQL Mastery', 'code' => 'SQL-201', 'fee' => 350],
            ['title' => 'API Security & Testing Fundamentals', 'code' => 'SEC-202', 'fee' => 400],
            ['title' => 'DevOps & Cloud Deployment', 'code' => 'DEV-301', 'fee' => 600],
        ];

        foreach ($courses as $index => $course) {
            if (Training::query()->where('code', $course['code'])->exists()) {
                continue;
            }

            Training::factory()->create([
                'title' => $course['title'],
                'code' => $course['code'],
                'fee' => $course['fee'],
                'trainer_id' => $trainers[$index % $trainers->count()]->id,
            ]);
        }
    }
}
