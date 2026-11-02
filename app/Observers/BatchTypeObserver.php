<?php

namespace App\Observers;

use App\Models\BatchType;

class BatchTypeObserver
{
    /**
     * Handle the BatchType "created" event.
     */
    public function created(BatchType $batchType): void
    {
        //
    }

    /**
     * Handle the BatchType "updated" event.
     */
    public function updated(BatchType $batchType): void
    {
        //
    }

    /**
     * Handle the BatchType "deleted" event.
     */
    public function deleted(BatchType $batchType): void
    {
        //
    }

    /**
     * Handle the BatchType "restored" event.
     */
    public function restored(BatchType $batchType): void
    {
        //
    }

    /**
     * Handle the BatchType "force deleted" event.
     */
    public function forceDeleted(BatchType $batchType): void
    {
        //
    }
}
