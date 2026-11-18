<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Confirms to the customer that a payment was received, with the totals as
 * they stood when it was recorded (not when the queue happens to send it).
 */
class PaymentRecorded extends LoggedNotification
{
    public function __construct(
        public Payment $payment,
        public int $amountPaidToDate,
        public int $balanceRemaining,
    ) {
        parent::__construct();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment;
        $booking = $payment->booking;

        return (new MailMessage)
            ->subject('Payment received: UGX '.number_format($payment->amount)." ({$booking->reference})")
            ->greeting("Hello {$booking->customer_name},")
            ->line('Thank you. We have received your payment for your '.$booking->event_type->label().' on '.$booking->event_date->format('l, j F Y').'.')
            ->line('**Amount:** UGX '.number_format($payment->amount))
            ->line("**Receipt number:** {$payment->receipt_number}")
            ->line('**Paid by:** '.$payment->method->label().($payment->reference ? " (ref {$payment->reference})" : ''))
            ->line('**Total paid so far:** UGX '.number_format($this->amountPaidToDate))
            ->line('**Balance remaining:** UGX '.number_format($this->balanceRemaining))
            ->line('Questions? Call or WhatsApp us on 0775 847 855.')
            ->salutation('Lekobosi Restaurant and Outside Catering');
    }
}
