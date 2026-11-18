<?php

namespace App\Enums;

/**
 * A payment is never deleted: mistakes are voided with a reason.
 */
enum PaymentStatus: string
{
    case Confirmed = 'confirmed';
    case Voided = 'voided';

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Confirmed',
            self::Voided => 'Voided',
        };
    }
}
