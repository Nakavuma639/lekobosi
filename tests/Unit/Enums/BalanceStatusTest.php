<?php

use App\Enums\BalanceStatus;

it('derives the payment status from the quote and amount paid', function (int $quote, int $paid, BalanceStatus $expected) {
    expect(BalanceStatus::for($quote, $paid))->toBe($expected);
})->with([
    'nothing paid' => [4_000_000, 0, BalanceStatus::Unpaid],
    'deposit paid' => [4_000_000, 1_000_000, BalanceStatus::Partial],
    'one shilling short' => [4_000_000, 3_999_999, BalanceStatus::Partial],
    'paid exactly' => [4_000_000, 4_000_000, BalanceStatus::Paid],
    'free event' => [0, 0, BalanceStatus::Unpaid],
]);
