<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Support\Facades\Auth;

class PaymentObserver
{
    /**
     * Handle the Payment "creating" event.
     */
    public function creating(Payment $payment): void
    {
        if (empty($payment->registrar_id) && Auth::check()) {
            $payment->registrar_id = Auth::id();
        }
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $student = $payment->student;
        $training = $payment->relationLoaded('training')
            ? $payment->training
            : ($student?->training ?? $payment->training);

        if ($training !== null && ! $payment->relationLoaded('training')) {
            $payment->setRelation('training', $training);
        }

        if ($student !== null && $student->fee === null && $training !== null) {
            $student->update(['fee' => (int) $training->fee]);
        }

        $recipient = $student?->trainee ?? ($student?->trainee_id ? User::find($student->trainee_id) : null);

        if ($recipient !== null && ! empty($recipient->email)) {
            $recipient->notify(new PaymentReceivedNotification($payment));
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
