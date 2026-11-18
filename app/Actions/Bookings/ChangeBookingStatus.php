<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Exceptions\BookingDateFullException;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\User;
use App\Services\BookingAvailability;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeBookingStatus
{
    public function __construct(private BookingAvailability $availability) {}

    /**
     * Approve or reject a booking and record the change in its history.
     *
     * Everything happens in one transaction. Approval locks every booking on
     * the event date first, so two approvals for the same date run one after
     * the other and the capacity check can't be raced.
     *
     * @throws ValidationException when the transition isn't allowed or a reason is missing.
     * @throws BookingDateFullException when approving would overbook the date.
     */
    public function handle(Booking $booking, BookingStatus $newStatus, User $decidedBy, ?string $note = null, ?int $quotedAmount = null): Booking
    {
        $note = $note === null ? null : (trim(strip_tags($note)) ?: null);

        if ($newStatus === BookingStatus::Rejected && $note === null) {
            throw ValidationException::withMessages(['note' => 'Give the customer a reason for rejecting the booking.']);
        }

        $this->useReadCommittedIsolation();

        return DB::transaction(function () use ($booking, $newStatus, $decidedBy, $note, $quotedAmount): Booking {
            // A single indexed equality on event_date keeps the lock to that one
            // date. The booking being decided is in this set too.
            $lockedBookings = Booking::query()
                ->where('event_date', $booking->event_date->toDateString())
                ->lockForUpdate()
                ->get();

            /** @var Booking $booking */
            $booking = $lockedBookings->firstWhere('id', $booking->getKey())
                ?? Booking::query()->lockForUpdate()->findOrFail($booking->getKey());
            $previousStatus = $booking->status;

            if (! $previousStatus->canTransitionTo($newStatus)) {
                throw ValidationException::withMessages([
                    'status' => "A booking that is {$previousStatus->label()} can't be changed to {$newStatus->label()}.",
                ]);
            }

            if ($newStatus === BookingStatus::Approved) {
                $approvedOnDate = $lockedBookings
                    ->where('status', BookingStatus::Approved)
                    ->where('id', '!=', $booking->id)
                    ->count();

                if ($approvedOnDate >= $this->availability->capacity()) {
                    throw new BookingDateFullException($booking->event_date);
                }
            }

            $booking->status = $newStatus;
            $booking->status_changed_at = now();
            $booking->decided_by = $decidedBy->id;
            $booking->decision_note = $note;

            if ($quotedAmount !== null) {
                $booking->quoted_amount = $quotedAmount;
            }

            $booking->save();

            $history = new BookingStatusHistory;
            $history->booking_id = $booking->id;
            $history->from_status = $previousStatus;
            $history->to_status = $newStatus;
            $history->changed_by = $decidedBy->id;
            $history->note = $note;
            $history->save();

            return $booking;
        });
    }

    /**
     * On MySQL/MariaDB, run the next transaction at READ COMMITTED. Approvals
     * for the same date still queue up behind each other's row locks (so the
     * capacity check is safe), but InnoDB takes no gap locks, so approvals on
     * neighbouring dates don't block each other.
     */
    private function useReadCommittedIsolation(): void
    {
        if (DB::transactionLevel() === 0 && in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        }
    }
}
