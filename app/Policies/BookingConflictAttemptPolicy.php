<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class BookingConflictAttemptPolicy
{
    /**
     * Only the owner reviews requests that were turned away (spec §7.4).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
