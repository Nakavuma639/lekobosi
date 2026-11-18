<?php

namespace App\Support;

class BookingReference
{
    /**
     * Characters that can't be confused when read aloud or copied by hand
     * (no 0/O, 1/I/L).
     */
    public const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public const PREFIX = 'LKB-';

    public const LENGTH = 8;

    /**
     * Generate a random, non-sequential reference such as LKB-7K3QX9MD.
     */
    public static function generate(): string
    {
        $maxIndex = strlen(self::ALPHABET) - 1;
        $code = '';

        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, $maxIndex)];
        }

        return self::PREFIX.$code;
    }

    /**
     * Tidy a reference typed by a customer: trim and uppercase.
     */
    public static function normalize(string $reference): string
    {
        return mb_strtoupper(trim($reference));
    }
}
