<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Acknowledges a customer's booking request and gives them their reference.
 */
class BookingReceived extends LoggedNotification
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

        return (new MailMessage)
            ->subject("We've received your booking request ({$booking->reference})")
            ->greeting("Hello {$booking->customer_name},")
            ->line('Thank you for choosing Lekobosi. We have received your catering request.')
            ->line("**Your booking reference:** {$booking->reference}")
            ->line("**Event:** {$booking->event_type->label()}, {$booking->event_date->format('l, j F Y')}")
            ->line('The owner will review your request and let you know the decision. Your date is not held until the booking is approved.')
            ->action('Track your booking', route('bookings.track', ['reference' => $booking->reference]))
            ->line('Questions? Call or WhatsApp us on 0775 847 855.')
            ->salutation('Lekobosi Restaurant and Outside Catering');
    }
}
