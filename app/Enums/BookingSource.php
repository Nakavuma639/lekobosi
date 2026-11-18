<?php

namespace App\Enums;

/**
 * How a booking reached the business.
 */
enum BookingSource: string
{
    case Online = 'online';
    case Phone = 'phone';
    case WalkIn = 'walk_in';

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Online => 'Website',
            self::Phone => 'Phone',
            self::WalkIn => 'Walk-in',
        };
    }
}
