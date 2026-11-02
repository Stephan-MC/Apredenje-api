<?php

namespace App\Observers;

use App\Models\Training;

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
