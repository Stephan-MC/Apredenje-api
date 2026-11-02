<?php

namespace App\Models;

use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('batch_type_id', 'session_id', 'trainer_id')]
class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory, HasUlids;

    public function batchType(): BelongsTo
    {
        return $this->belongsTo(BatchType::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function trainees()
    {
        return Trainee::whereHas('enrolledTrainings.sessions', function ($query) {
            $query->where('id', $this->session_id);
        })->get();
    }
}
