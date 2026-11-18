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
     * Tidy a reference typed by a customer: uppercase, drop spaces, and add
     * the LKB- prefix if only the eight-character code was typed.
     */
    public static function normalize(string $reference): string
    {
        $reference = mb_strtoupper(preg_replace('/\s+/', '', $reference));

        if (strlen($reference) === self::LENGTH && ! str_starts_with($reference, self::PREFIX)) {
            return self::PREFIX.$reference;
        }

        return $reference;
    }
}
