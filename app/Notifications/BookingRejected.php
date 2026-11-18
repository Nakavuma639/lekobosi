<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Tells the customer their request was declined, or their approved booking
 * cancelled, with the owner's reason.
 */
class BookingRejected extends LoggedNotification
{
    public function __construct(public Booking $booking, public bool $wasCancelled = false)
    {
        parent::__construct();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $date = $booking->event_date->format('l, j F Y');

        $mail = (new MailMessage)
            ->subject($this->wasCancelled
                ? "Your booking has been cancelled ({$booking->reference})"
                : "About your booking request ({$booking->reference})")
            ->greeting("Hello {$booking->customer_name},")
            ->line($this->wasCancelled
                ? "We're sorry, but we have had to cancel your {$booking->event_type->label()} booking on {$date}."
                : "We're sorry, but we can't cater for your {$booking->event_type->label()} on {$date}.");

        if ($booking->decision_note) {
            $mail->line("**Reason:** {$booking->decision_note}");
        }

        return $mail
            ->line('We would be glad to help with another date.')
            ->action('Request another date', route('bookings.create'))
            ->line('Call or WhatsApp us on 0775 847 855 to talk it through.')
            ->salutation('Lekobosi Restaurant and Outside Catering');
    }
}
