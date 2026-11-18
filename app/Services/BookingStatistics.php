<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Payment;
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

    /**
     * Money totals across bookings, in UGX.
     *
     * - quoted: sum of quotes on approved bookings
     * - collected: confirmed payments on approved bookings
     * - outstanding: quoted minus collected (never negative, as overpayment is refused)
     * - held_on_cancelled: confirmed payments on bookings later cancelled; the
     *   owner settles these outside the system
     *
     * @return array{quoted: int, collected: int, outstanding: int, held_on_cancelled: int}
     */
    public function paymentTotals(): array
    {
        $quoted = (int) Booking::query()
            ->where('status', BookingStatus::Approved)
            ->sum('quoted_amount');

        $collectedByStatus = Payment::query()
            ->confirmed()
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->whereIn('bookings.status', [BookingStatus::Approved, BookingStatus::Rejected])
            ->groupBy('bookings.status')
            ->selectRaw('bookings.status as booking_status, SUM(payments.amount) as total')
            ->pluck('total', 'booking_status');

        $collected = (int) ($collectedByStatus[BookingStatus::Approved->value] ?? 0);

        return [
            'quoted' => $quoted,
            'collected' => $collected,
            'outstanding' => max($quoted - $collected, 0),
            'held_on_cancelled' => (int) ($collectedByStatus[BookingStatus::Rejected->value] ?? 0),
        ];
    }
}
