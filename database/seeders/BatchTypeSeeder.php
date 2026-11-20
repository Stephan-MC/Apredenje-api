<?php

namespace Database\Seeders;

use App\Models\BatchType;
use Illuminate\Database\Seeder;

class BatchTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Morning', 'Afternoon', 'Evening', 'Weekend', 'Online', 'Hybrid'] as $name) {
            BatchType::query()->firstOrCreate(['name' => $name]);
        }
    }
}
