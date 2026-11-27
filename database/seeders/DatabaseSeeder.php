<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
        } else {
            $this->call([
                UserSeeder::class,
                AdminSeeder::class,
                StaffSeeder::class,
                TrainerSeeder::class,
                TraineeSeeder::class,
                BatchTypeSeeder::class,
                TrainingSeeder::class,
                SessionSeeder::class,
                BatchSeeder::class,
                QuizSeeder::class,
                StudentSeeder::class,
                ResultSeeder::class,
                PaymentSeeder::class,
                InternshipSeeder::class,
            ]);
        }
    }
}
