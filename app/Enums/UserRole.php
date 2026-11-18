<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Staff = 'staff';
    case Admin = 'admin';

    /**
     * Whether this role may use the back-office (admin/staff) area.
     */
    public function isBackOffice(): bool
    {
        return $this === self::Staff || $this === self::Admin;
    }

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Staff => 'Staff',
            self::Admin => 'Administrator',
        };
    }
}
