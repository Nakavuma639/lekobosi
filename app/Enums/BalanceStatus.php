<?php

namespace App\Enums;

/**
 * Where a booking stands against its quote. Derived, never stored.
 */
enum BalanceStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    /**
     * Work out the status from the quote and the confirmed payments.
     */
    public static function for(int $quotedAmount, int $amountPaid): self
    {
        return match (true) {
            $amountPaid <= 0 => self::Unpaid,
            $amountPaid >= $quotedAmount => self::Paid,
            default => self::Partial,
        };
    }

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Partial => 'Part paid',
            self::Paid => 'Paid in full',
        };
    }
}
