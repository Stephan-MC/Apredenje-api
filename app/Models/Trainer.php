<?php

namespace App\Models;

use App\Observers\TrainerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(TrainerObserver::class)]
class Trainer extends User
{
    public function trainings()
    {
        return $this->hasMany(Training::class, 'trainer_id');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class, 'trainer_id');
    }
}
