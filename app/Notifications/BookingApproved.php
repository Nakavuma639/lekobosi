<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Tells the customer their booking is confirmed.
 */
class BookingApproved extends LoggedNotification
{
    public function __construct(public Booking $booking)
    {
        parent::__construct();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;

        $mail = (new MailMessage)
            ->subject("Your booking is confirmed ({$booking->reference})")
            ->greeting("Hello {$booking->customer_name},")
            ->line("Good news: your {$booking->event_type->label()} on **{$booking->event_date->format('l, j F Y')}** is confirmed.");

        if ($booking->quoted_amount !== null) {
            $mail->line('**Agreed quote:** UGX '.number_format($booking->quoted_amount));
        }

        return $mail
            ->line('We will contact you to finalise the menu and the deposit.')
            ->action('View your booking status', route('bookings.track', ['reference' => $booking->reference]))
            ->line('Questions? Call or WhatsApp us on 0775 847 855.')
            ->salutation('Lekobosi Restaurant and Outside Catering');
    }
}
