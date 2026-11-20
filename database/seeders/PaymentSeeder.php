<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::query()->with('training')->get();

        if ($students->isEmpty()) {
            Payment::factory()->count(5)->create();

            return;
        }

        $staff = Staff::query()->get();

        foreach ($students as $student) {
            $registrarId = $student->registrar_id
                ?? ($staff->isNotEmpty() ? $staff->random()->id : Staff::factory()->create()->id);

            Payment::factory()->create([
                'student_id' => $student->id,
                'registrar_id' => $registrarId,
                'amount' => $student->fee ?? $student->training?->fee ?? 250,
            ]);
        }
    }
}
