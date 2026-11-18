<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalise a Ugandan phone number to E.164 (+256XXXXXXXXX).
     *
     * Accepts 07XXXXXXXX, 2567XXXXXXXX and +2567XXXXXXXX, with optional spaces,
     * dashes or brackets. Returns null when the input is not a Ugandan number.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/[\s\-().]/', '', trim($value));

        if (preg_match('/^(?:\+?256|0)([347]\d{8})$/', $digits, $matches) !== 1) {
            return null;
        }

        return '+256'.$matches[1];
    }
}
