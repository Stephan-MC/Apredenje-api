<?php

namespace App\Models;

use Database\Factories\ResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table('results')]
#[Fillable('trainee_id', 'quiz_id', 'mark')]
class Result extends Pivot
{
    /** @use HasFactory<ResultFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'mark' => 'integer',
        ];
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class, 'trainee_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
