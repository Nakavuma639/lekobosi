<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Tells the owner a new booking request has arrived.
 */
class NewBookingSubmitted extends LoggedNotification
{
    public function __construct(public Booking $booking, public bool $dateAlreadyTaken = false)
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
            ->subject("New booking request: {$booking->event_type->label()} on {$booking->event_date->format('j M Y')}")
            ->greeting('New booking request')
            ->line("**{$booking->customer_name}** has asked for catering.")
            ->line("**Reference:** {$booking->reference}")
            ->line("**Event:** {$booking->event_type->label()}, {$booking->event_date->format('l, j F Y')}")
            ->line("**Location:** {$booking->event_location}")
            ->line('**Guests:** '.number_format($booking->guest_count))
            ->line("**Phone:** {$booking->customer_phone}");

        if ($this->dateAlreadyTaken) {
            $mail->line('Note: another booking is already approved for this date.');
        }

        return $mail
            ->action('Review the booking', route('admin.bookings.show', $booking))
            ->salutation('Lekobosi booking system');
    }
}
