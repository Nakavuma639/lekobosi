<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Owner and staff see payments and the Payments and Balances screen.
     */
    public function viewAny(User $user): bool
    {
        return $user->isBackOffice();
    }

    /**
     * Owner and staff can open and print a receipt.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $user->isBackOffice();
    }

    /**
     * Only the owner voids payments, and only confirmed ones (spec §6.5).
     */
    public function void(User $user, Payment $payment): bool
    {
        return $user->hasRole(UserRole::Admin) && $payment->status === PaymentStatus::Confirmed;
    }
}
