<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBookingQuote
{
    /**
     * Set or change the agreed price. Allowed while the booking is pending or
     * approved, and never below what the customer has already paid.
     *
     * @throws ValidationException when the booking is rejected or the quote is below the amount paid.
     */
    public function handle(Booking $booking, int $quotedAmount): Booking
    {
        return DB::transaction(function () use ($booking, $quotedAmount): Booking {
            /** @var Booking $booking */
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status === BookingStatus::Rejected) {
                throw ValidationException::withMessages(['quoted_amount' => "The quote can't be changed on a rejected booking."]);
            }

            $amountPaid = (int) $booking->payments()->confirmed()->sum('amount');

            if ($quotedAmount < $amountPaid) {
                throw ValidationException::withMessages([
                    'quoted_amount' => "The quote can't be less than the UGX ".number_format($amountPaid).' already paid. Void a payment first if it was recorded by mistake.',
                ]);
            }

            $booking->quoted_amount = $quotedAmount;
            $booking->save();

            return $booking;
        });
    }
}
