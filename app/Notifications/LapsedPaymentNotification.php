<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LapsedPaymentNotification extends Notification
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
            'type' => 'lapsed_payment',
            'loan_id' => $this->loan->id ?? null,
            'payment_id' => $this->payment->id ?? null,
            'message' => 'Payment lapsed for loan ' . ($this->loan->id ?? ''),
        ];
    }
}
