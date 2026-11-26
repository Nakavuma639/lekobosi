<?php

namespace App\Actions\Bookings;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Models\Booking;
use App\Models\BookingConflictAttempt;
use App\Models\BookingStatusHistory;
use App\Models\User;
use App\Services\BookingAvailability;
use App\Services\NotificationDispatcher;
use App\Support\BookingReference;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SubmitBooking
{
    private const MAX_REFERENCE_ATTEMPTS = 5;

    public function __construct(
        private BookingAvailability $availability,
        private NotificationDispatcher $notifications,
    ) {}

    /**
     * Store a new booking request as Pending, with its first history row.
     *
     * A request for a full date is refused and recorded as a conflict attempt
     * so the owner can follow up on the lost demand.
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
     * @throws ValidationException when the date is already fully booked.
     */
    public function handle(array $details, ?User $customer = null, BookingSource $source = BookingSource::Online, ?User $enteredBy = null): Booking
    {
       $eventDate = CarbonImmutable::parse($details['event_date']);
       $requestedGuests = (int) $details['guest_count'];
       $approvedGuests = $this->availability->approvedGuestsOn($eventDate);
       $dailyCapacity = 2000;

if (($approvedGuests + $requestedGuests) > $dailyCapacity) {
    $this->recordConflictAttempt($details);

    throw ValidationException::withMessages([
        'date' => 'This date does not have enough remaining guest capacity. Please choose another date or reduce the number of guests.',
    ]);
}
        $booking = DB::transaction(function () use ($details, $customer, $source, $enteredBy): Booking {
            $booking = new Booking;
            $booking->user_id = $customer?->id;
            $booking->created_by = $enteredBy?->id;
            $booking->source = $source;
            $booking->customer_name = $details['customer_name'];
            $booking->customer_email = $details['customer_email'];
            $booking->customer_phone = $details['customer_phone'];
            $booking->whatsapp_number = $details['whatsapp_number'];
            $booking->event_type = $details['event_type'];
            $booking->event_date = $details['event_date'];
            $booking->event_location = $details['event_location'];
            $booking->guest_count = $details['guest_count'];
            $booking->extra_requirements = $details['extra_requirements'];
            $booking->status = BookingStatus::Pending;
            $booking->status_changed_at = now();

            $this->saveWithUniqueReference($booking);

            $history = new BookingStatusHistory;
            $history->booking_id = $booking->id;
            $history->from_status = null;
            $history->to_status = BookingStatus::Pending;
            $history->changed_by = $enteredBy?->id;
            $history->save();

            return $booking;
        });

        $this->notifications->bookingSubmitted($booking);

        return $booking;
    }

    /**
     * Save the booking, regenerating the reference if it collides.
     */
    private function saveWithUniqueReference(Booking $booking): void
    {
        for ($attempt = 1; $attempt <= self::MAX_REFERENCE_ATTEMPTS; $attempt++) {
            $booking->reference = BookingReference::generate();

            try {
                DB::transaction(fn () => $booking->save());

                return;
            } catch (UniqueConstraintViolationException) {
                // Collision on the reference: try a fresh one.
            }
        }

        throw new RuntimeException('Could not generate a unique booking reference.');
    }

    /**
     * Keep a record of a request that was turned away for a full date.
     *
     * @param  array{customer_name: string, customer_email: ?string, customer_phone: string, event_type: EventType, event_date: CarbonImmutable}  $details
     */
    private function recordConflictAttempt(array $details): void
    {
        $attempt = new BookingConflictAttempt;
        $attempt->customer_name = $details['customer_name'];
        $attempt->customer_phone = $details['customer_phone'];
        $attempt->customer_email = $details['customer_email'];
        $attempt->event_type = $details['event_type'];
        $attempt->requested_date = $details['event_date'];
        $attempt->save();
    }
}
