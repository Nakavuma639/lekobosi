<?php

namespace App\Enums;

/**
 * How a customer paid. Payments are recorded by staff; there is no online
 * payment integration (spec §2.2).
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case MtnMomo = 'mtn_momo';
    case AirtelMoney = 'airtel_money';
    case BankTransfer = 'bank_transfer';
    case Other = 'other';

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::MtnMomo => 'MTN Mobile Money',
            self::AirtelMoney => 'Airtel Money',
            self::BankTransfer => 'Bank transfer',
            self::Other => 'Other',
        };
    }
}
