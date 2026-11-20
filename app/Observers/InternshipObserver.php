<?php

namespace App\Observers;

use App\Models\Internship;

class InternshipObserver
{
    /**
     * Handle the Internship "created" event.
     */
    public function created(Internship $internship): void
    {
        //
    }

    /**
     * Handle the Internship "updated" event.
     */
    public function updated(Internship $internship): void
    {
        //
    }

    /**
     * Handle the Internship "deleted" event.
     */
    public function deleted(Internship $internship): void
    {
        //
    }

    /**
     * Handle the Internship "restored" event.
     */
    public function restored(Internship $internship): void
    {
        //
    }

    /**
     * Handle the Internship "force deleted" event.
     */
    public function forceDeleted(Internship $internship): void
    {
        //
    }
}
