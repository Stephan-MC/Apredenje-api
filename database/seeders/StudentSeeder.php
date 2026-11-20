<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\Student;
use App\Models\Trainee;
use App\Models\Training;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainees = Trainee::query()->get();

        if ($trainees->isEmpty()) {
            $trainees = Trainee::factory()->count(5)->create();
        }

        $trainings = Training::query()->get();

        if ($trainings->isEmpty()) {
            $trainings = Training::factory()->count(3)->create();
        }

        $staff = Staff::query()->get();

        if ($staff->isEmpty()) {
            $staff = Staff::factory()->count(2)->create();
        }

        foreach ($trainees as $trainee) {
            $selectedTrainings = $trainings->shuffle()->take(min(2, $trainings->count()));

            foreach ($selectedTrainings as $training) {
                Student::factory()->create([
                    'trainee_id' => $trainee->id,
                    'training_id' => $training->id,
                    'registrar_id' => $staff->random()->id,
                    'fee' => (int) $training->fee,
                ]);
            }
        }
    }
}
