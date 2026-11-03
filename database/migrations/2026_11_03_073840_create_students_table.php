<?php

use App\Models\Training;
use App\Models\User;
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
        Schema::create('students', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(User::class, 'trainee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Training::class, 'training_id')->constrained('trainings')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'registrar_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('fee')->nullable();
            $table->string('fee_reason')->nullable();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
