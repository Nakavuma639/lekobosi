<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Only the owner sees and manages staff accounts (spec §6.5).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Only the owner creates staff accounts.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Only customers may delete their own account. Staff and the owner are
     * named on decisions and payments, so their accounts are disabled
     * instead, keeping the audit trail intact (spec §9).
     */
    public function delete(User $user, User $account): bool
    {
        return $user->is($account) && $account->hasRole(UserRole::Customer);
    }

    /**
     * The owner can enable or disable staff accounts, never an admin's
     * (including their own) or a customer's.
     */
    public function update(User $user, User $account): bool
    {
        return $user->hasRole(UserRole::Admin) && $account->hasRole(UserRole::Staff);
    }
}
