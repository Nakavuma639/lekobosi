<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;

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
     * Back office sees any booking; a customer sees only their own.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->isBackOffice() || ($booking->user_id !== null && $booking->user_id === $user->id);
    }

    /**
     * Only the owner approves, rejects or cancels bookings (spec §6.5, open question 6).
     */
    public function decide(User $user, Booking $booking): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
