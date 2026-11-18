<?php

namespace App\Support;

/**
 * UGX amounts are whole shillings stored as unsigned integers.
 */
class Money
{
    /**
     * Strip the separators people type ("4,500,000", "4 500 000", "UGX 4500000")
     * so the value can be validated as an integer. Anything else is left as
     * typed, so validation can reject it.
     */
    public static function cleanInput(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_replace('/^\s*UGX\s*|[\s,]/i', '', $value);
    }

    /**
     * Format an amount for display, e.g. "UGX 4,500,000".
     */
    public static function format(?int $amount): string
    {
        return $amount === null ? '—' : 'UGX '.number_format($amount);
    }
}
