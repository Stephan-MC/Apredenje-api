<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Trainee;
use Illuminate\Auth\Access\Response;

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
     * Handle the Trainee "deleting" event.
     */
    public function deleting(Trainee $trainee): void
    {
        if (Payment::query()->whereHas('student', fn ($query) => $query->where('trainee_id', $trainee->id))->exists()) {
            Response::deny('Cannot delete a trainee that already has payments.')
                ->withStatus(409)
                ->authorize();
        }
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
