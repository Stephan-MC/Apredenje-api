<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Training;
use Illuminate\Auth\Access\Response;

class TrainingObserver
{
    /**
     * Handle the Training "created" event.
     */
    public function created(Training $training): void
    {
        //
    }

    /**
     * Handle the Training "updated" event.
     */
    public function updated(Training $training): void
    {
        //
    }

    /**
     * Handle the Training "deleting" event.
     */
    public function deleting(Training $training): void
    {
        if (Payment::query()->whereHas('student', fn ($query) => $query->where('training_id', $training->id))->exists()) {
            Response::deny('Cannot delete a training that already has payments.')
                ->withStatus(409)
                ->authorize();
        }
    }

    /**
     * Handle the Training "deleted" event.
     */
    public function deleted(Training $training): void
    {
        //
    }

    /**
     * Handle the Training "restored" event.
     */
    public function restored(Training $training): void
    {
        //
    }

    /**
     * Handle the Training "force deleted" event.
     */
    public function forceDeleted(Training $training): void
    {
        //
    }
}
