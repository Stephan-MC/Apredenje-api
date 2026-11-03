<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

// #[ObservedBy()]
#[Fillable('trainee_id', 'training_id', 'registrar_id', 'fee', 'fee_reason')]
#[Table('students')]
class Student extends Pivot
{
    use HasUlids;

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
        return $this->belongsTo(Admin::class, 'registrar_id');
    }
}
