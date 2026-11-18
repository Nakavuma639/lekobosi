<?php

namespace App\Support;

use App\Models\Payment;
use Carbon\CarbonInterface;

/**
 * Sequential receipt numbers such as RCT-2026-00001. The count restarts each
 * calendar year (an assumption; the spec only gives the format).
 */
class ReceiptNumber
{
    public const PREFIX = 'RCT';

    /**
     * The next receipt number for the year. Callers must save inside a
     * transaction and retry on a unique-index collision, because two staff
     * recording at the same moment can both read the same "last" number.
     */
    public static function next(CarbonInterface $date): string
    {
        $prefix = self::PREFIX.'-'.$date->year.'-';

        $last = Payment::query()
            ->where('receipt_number', 'like', $prefix.'%')
            ->orderByDesc('receipt_number')
            ->value('receipt_number');

        $sequence = $last === null ? 1 : ((int) substr($last, strlen($prefix))) + 1;

        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
