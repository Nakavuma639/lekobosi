<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\CarbonImmutable;

/**
 * Live dashboard totals, computed from the database on every call (no cache),
 * so the owner always sees current numbers.
 */
class BookingStatistics
{
    /**
     * @return array{total: int, pending: int, approved: int, rejected: int, upcoming_30_days: int}
     */
    public function totals(): array
    {
        $today = CarbonImmutable::today();

        $row = Booking::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending', [BookingStatus::Pending->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved', [BookingStatus::Approved->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected', [BookingStatus::Rejected->value])
            ->selectRaw(
                'SUM(CASE WHEN status = ? AND event_date BETWEEN ? AND ? THEN 1 ELSE 0 END) as upcoming_30_days',
                [BookingStatus::Approved->value, $today->toDateString(), $today->addDays(30)->toDateString()],
            )
            ->first();

        return [
            'total' => (int) $row->total,
            'pending' => (int) $row->pending,
            'approved' => (int) $row->approved,
            'rejected' => (int) $row->rejected,
            'upcoming_30_days' => (int) $row->upcoming_30_days,
        ];
    }
}
