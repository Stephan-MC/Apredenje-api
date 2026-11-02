<?php

use App\Models\Quiz;
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
        Schema::create('results', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(User::class, 'trainee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Quiz::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('mark');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
