<?php

namespace App\Models;

use App\Observers\TrainerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(TrainerObserver::class)]
#[Table('users')]
class Trainer extends User
{
    use HasFactory, HasUlids;

    public function newQuery(): Builder
    {
        return parent::newQuery()->whereIs('trainer');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class, 'trainer_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'trainer_id');
    }
}
