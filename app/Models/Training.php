<?php

namespace App\Models;

use App\Observers\TrainingObserver;
use Database\Factories\TrainingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(TrainingObserver::class)]
#[Fillable('title', 'code', 'trainer_id', 'fee')]
class Training extends Model
{
    /** @use HasFactory<TrainingFactory> */
    use HasFactory,HasUlids;

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
