<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Models\Booking;
use App\Support\BookingReference;
use Carbon\CarbonInterface;

class TrackBooking
{
    /**
     * Look up a booking by its public reference and return only what the
     * customer may see without logging in: no names, contact details,
     * locations or money. The rejection reason is included only when rejected.
     *
     * @return array{
     *     reference: string,
     *     status: BookingStatus,
     *     event_type: EventType,
     *     event_date: CarbonInterface,
     *     status_changed_at: ?CarbonInterface,
     *     decision_note: ?string,
     * }|null
     */
    public function handle(string $reference): ?array
    {
        $reference = BookingReference::normalize($reference);

        if (strlen($reference) > 16) {
            return null;
        }

        $booking = Booking::query()
            ->select(['reference', 'status', 'event_type', 'event_date', 'status_changed_at', 'decision_note'])
            ->where('reference', $reference)
            ->first();

        if ($booking === null) {
            return null;
        }

        return [
            'reference' => $booking->reference,
            'status' => $booking->status,
            'event_type' => $booking->event_type,
            'event_date' => $booking->event_date,
            'status_changed_at' => $booking->status_changed_at,
            'decision_note' => $booking->status === BookingStatus::Rejected ? $booking->decision_note : null,
        ];
    }
}
