<?php

namespace App\Services;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Basic booking and payment reports for a date range (spec §7.4).
 */
class ReportBuilder
{
    /**
     * Bookings whose event falls in the range, with counts by status, event
     * type and source.
     *
     * @return array{
     *     bookings: Collection<int, Booking>,
     *     byStatus: array<string, int>,
     *     byEventType: array<string, int>,
     *     bySource: array<string, int>,
     *     guests: int,
     * }
     */
    public function bookings(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $bookings = Booking::query()
            ->whereBetween('event_date', [$from->toDateString(), $to->toDateString()])
            ->withAmountPaid()
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        return [
            'bookings' => $bookings,
            'byStatus' => $this->countBy($bookings, 'status', BookingStatus::cases()),
            'byEventType' => $this->countBy($bookings, 'event_type', EventType::cases()),
            'bySource' => $this->countBy($bookings, 'source', BookingSource::cases()),
            'guests' => (int) $bookings->where('status', BookingStatus::Approved)->sum('guest_count'),
        ];
    }

    /**
     * Payments received in the range, with confirmed totals by method and
     * voided payments kept separate.
     *
     * @return array{
     *     payments: Collection<int, Payment>,
     *     confirmedTotal: int,
     *     voidedTotal: int,
     *     byMethod: array<string, int>,
     * }
     */
    public function payments(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $payments = Payment::query()
            ->whereBetween('received_at', [$from->startOfDay(), $to->endOfDay()])
            ->with(['booking:id,reference,customer_name,event_date,event_type', 'recorder:id,name'])
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();

        $confirmed = $payments->where('status', PaymentStatus::Confirmed);

        $byMethod = [];
        foreach (PaymentMethod::cases() as $method) {
            $byMethod[$method->label()] = (int) $confirmed->where('method', $method)->sum('amount');
        }

        return [
            'payments' => $payments,
            'confirmedTotal' => (int) $confirmed->sum('amount'),
            'voidedTotal' => (int) $payments->where('status', PaymentStatus::Voided)->sum('amount'),
            'byMethod' => $byMethod,
        ];
    }

    /**
     * Count models per enum case, keeping every case (zeros included) in order.
     *
     * @param  Collection<int, Booking>  $bookings
     * @param  list<BookingStatus|EventType|BookingSource>  $cases
     * @return array<string, int>
     */
    private function countBy(Collection $bookings, string $attribute, array $cases): array
    {
        $counts = [];
        foreach ($cases as $case) {
            $counts[$case->label()] = $bookings->where($attribute, $case)->count();
        }

        return $counts;
    }
}
