<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PaymentCollectedNotification extends Notification
{
    use Queueable;

    protected $loan;
    protected $payment;

    public function __construct($loan, $payment)
    {
        $this->loan = $loan;
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment_collected',
            'loan_id' => $this->loan->id ?? null,
            'client_id' => $this->loan->client_id ?? null,
            'payment_id' => $this->payment->id ?? null,
            'amount' => $this->payment->collection ?? 0,
            'message' => 'Payment received: ' . number_format($this->payment->collection ?? 0, 2),
        ];
    }
}
