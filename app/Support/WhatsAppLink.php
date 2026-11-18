<?php

namespace App\Support;

use App\Models\Booking;

/**
 * Builds wa.me links that open WhatsApp with a ready-written message, so
 * staff can notify a customer with one tap without a WhatsApp Business API.
 */
class WhatsAppLink
{
    /**
     * The message for the booking's current status.
     */
    public static function messageFor(Booking $booking): string
    {
        return __('whatsapp.'.$booking->status->value, [
            'name' => $booking->customer_name,
            'reference' => $booking->reference,
            'event' => $booking->event_type->label(),
            'date' => $booking->event_date->format('l, j F Y'),
            'reason' => $booking->decision_note ?? 'no reason given',
        ]);
    }

    /**
     * A wa.me link to the customer's WhatsApp number with the status message.
     */
    public static function forBooking(Booking $booking): string
    {
        $number = ltrim($booking->whatsapp_number ?? $booking->customer_phone, '+');

        return 'https://wa.me/'.$number.'?text='.rawurlencode(self::messageFor($booking));
    }
}
