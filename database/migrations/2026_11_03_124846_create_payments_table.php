<?php

use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Student::class, 'student_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Staff::class, 'registrar_id')
                ->nullable()
                ->comment('The Person who collected the money and issue a physical receipt to the student')
                ->constrained('users')
                ->nullOnDelete();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
