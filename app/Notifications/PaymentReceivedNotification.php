<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

#[DeleteWhenMissingModels]
class PaymentReceivedNotification extends Notification implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Payment $payment) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $trainingTitle = $this->payment->training?->title ?? 'Training';
        $fee = $this->payment->amount ?? $this->payment->student?->fee ?? $this->payment->training?->fee;

        $mail = (new MailMessage)
            ->subject("Payment Receipt - {$trainingTitle}")
            ->line("We have received your payment for {$trainingTitle}.")
            ->line("Receipt ID: {$this->payment->id}");

        if ($fee !== null) {
            $mail->line("Amount: {$fee}");
        }

        return $mail->line('Thank you for your payment!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'student_id' => $this->payment->student_id,
            'training_id' => $this->payment->training?->id,
        ];
    }
}
