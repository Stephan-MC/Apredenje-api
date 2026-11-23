<?php

use App\Models\Trainer;
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
        $driver = Schema::connection($this->getConnection())->getConnection()->getDriverName();

        Schema::create('trainings', function (Blueprint $table) use ($driver) {
            $table->ulid('id')->primary();
            $title = $table->string('title');

            if (in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
                $title->fulltext();
            } else {
                $title->index();
            }

            $table->string('code')->unique();
            $table->foreignUlidFor(Trainer::class)
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->decimal('fee', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
