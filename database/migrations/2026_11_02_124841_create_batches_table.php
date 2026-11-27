<?php

use App\Models\BatchType;
use App\Models\Session;
use App\Models\Trainer;
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
        Schema::create('batches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(BatchType::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Session::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Trainer::class)->constrained('users')->nullOnDelete();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
