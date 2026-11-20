<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\BatchType;
use App\Models\Session;
use App\Models\Trainer;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batchTypes = BatchType::query()->get();

        if ($batchTypes->isEmpty()) {
            $batchTypes = BatchType::factory()->count(3)->create();
        }

        $sessions = Session::query()->with('training')->get();

        if ($sessions->isEmpty()) {
            $sessions = Session::factory()->count(3)->create()->load('training');
        }

        $trainers = Trainer::query()->get();

        if ($trainers->isEmpty()) {
            $trainers = Trainer::factory()->count(2)->create();
        }

        foreach ($sessions as $session) {
            Batch::factory()->create([
                'batch_type_id' => $batchTypes->random()->id,
                'session_id' => $session->id,
                'trainer_id' => $session->training?->trainer_id ?? $trainers->random()->id,
            ]);
        }
    }
}
