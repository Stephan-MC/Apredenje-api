<?php

namespace App\Models;

use App\Observers\StudentObserver;
use App\Policies\StudentPolicy;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[ObservedBy(StudentObserver::class)]
#[UsePolicy(StudentPolicy::class)]
#[Fillable('trainee_id', 'training_id', 'registrar_id', 'fee', 'fee_reason')]
#[Table('students')]
class Student extends Pivot
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'fee' => 'integer',
        ];
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class, 'trainee_id');
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrar_id');
    }

    /**
     * Get the payments for the student registration.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }
}
