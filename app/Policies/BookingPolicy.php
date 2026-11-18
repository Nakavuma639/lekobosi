<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Owner and staff can see every booking.
     */
    public function viewAny(User $user): bool
    {
        return $user->isBackOffice();
    }

    /**
     * Back office sees any booking; a customer sees only their own. Anyone else
     * gets a 404, so a guessed reference doesn't confirm a booking exists.
     */
    public function view(User $user, Booking $booking): Response
    {
        return $user->isBackOffice() || ($booking->user_id !== null && $booking->user_id === $user->id)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Only the owner approves, rejects or cancels bookings (spec §6.5, open question 6).
     */
    public function decide(User $user, Booking $booking): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Owner and staff set and edit the quote.
     */
    public function updateQuote(User $user, Booking $booking): bool
    {
        return $user->isBackOffice();
    }

    /**
     * Owner and staff record payments.
     */
    public function recordPayment(User $user, Booking $booking): bool
    {
        return $user->isBackOffice();
    }
}
