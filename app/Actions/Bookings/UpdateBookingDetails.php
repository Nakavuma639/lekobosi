<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Models\Booking;
use App\Services\BookingAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBookingDetails
{
    public function __construct(private BookingAvailability $availability) {}

    /**
     * Correct a pending booking's details (spec §7.4). Once a booking is
     * decided its details are fixed; only the quote can still change.
     *
     * @param  array{
     *     customer_name: string,
     *     customer_email: ?string,
     *     customer_phone: string,
     *     whatsapp_number: ?string,
     *     event_type: EventType,
     *     event_date: CarbonImmutable,
     *     event_location: string,
     *     guest_count: int,
     *     extra_requirements: ?string,
     * }  $details
     *
     * @throws ValidationException when the booking is no longer pending or the new date is full.
     */
    public function handle(Booking $booking, array $details): Booking
    {
        return DB::transaction(function () use ($booking, $details): Booking {
            /** @var Booking $booking */
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status !== BookingStatus::Pending) {
                throw ValidationException::withMessages(['fullname' => 'Only pending bookings can be edited.']);
            }

            $dateChanged = ! $booking->event_date->isSameDay($details['event_date']);

            if ($dateChanged && $this->availability->isFull($details['event_date'])) {
                throw ValidationException::withMessages(['date' => 'That date is already booked. Please choose another date.']);
            }

            $booking->customer_name = $details['customer_name'];
            $booking->customer_email = $details['customer_email'];
            $booking->customer_phone = $details['customer_phone'];
            $booking->whatsapp_number = $details['whatsapp_number'];
            $booking->event_type = $details['event_type'];
            $booking->event_date = $details['event_date'];
            $booking->event_location = $details['event_location'];
            $booking->guest_count = $details['guest_count'];
            $booking->extra_requirements = $details['extra_requirements'];
            $booking->save();

            return $booking;
        });
    }
}
