<?php

namespace App\Observers;

use App\Models\Trainee;

class TraineeObserver
{
    /**
     * Handle the Trainee "created" event.
     */
    public function created(Trainee $trainee): void
    {
        $trainee->assign('trainee');
    }

    /**
     * Handle the Trainee "updated" event.
     */
    public function updated(Trainee $trainee): void
    {
        //
    }

    /**
     * Handle the Trainee "deleted" event.
     */
    public function deleted(Trainee $trainee): void
    {
        //
    }

    /**
     * Handle the Trainee "restored" event.
     */
    public function restored(Trainee $trainee): void
    {
        //
    }

    /**
     * Handle the Trainee "force deleted" event.
     */
    public function forceDeleted(Trainee $trainee): void
    {
        //
    }
}
