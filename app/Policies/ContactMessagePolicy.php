<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    /**
     * Owner and staff can read the inbox (spec §6.5).
     */
    public function viewAny(User $user): bool
    {
        return $user->isBackOffice();
    }

    /**
     * Owner and staff can read a message.
     */
    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->isBackOffice();
    }

    /**
     * Only the owner manages the inbox, e.g. marks messages as read.
     */
    public function markAsRead(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
