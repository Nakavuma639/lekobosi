<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * The single source of truth for whether a date can take another event.
 *
 * Only approved bookings block a date. A date is full once its approved
 * bookings reach the configured daily capacity.
 */
class BookingAvailability
{
    /**
     * How many approved events the business can cater for on one day.
     */
    public function capacity(): int
    {
        return max(1, (int) config('lekobosi.bookings.max_events_per_day'));
    }

    /**
     * The first date a new booking can be requested for.
     */
    public function earliestBookableDate(): CarbonImmutable
    {
        return CarbonImmutable::today()->addDays((int) config('lekobosi.bookings.min_lead_days'));
    }

    /**
     * The last date a new booking can be requested for.
     */
    public function latestBookableDate(): CarbonImmutable
    {
        return CarbonImmutable::today()->addYears((int) config('lekobosi.bookings.max_lead_years'));
    }

    /**
     * Whether the date has no room for another approved booking.
     */
    public function isFull(CarbonInterface $date): bool
    {
        return $this->approvedCountOn($date) >= $this->capacity();
    }

    /**
     * Number of approved bookings on the date.
     */
    public function approvedCountOn(CarbonInterface $date): int
    {
        return Booking::query()
            ->approved()
            ->where('event_date', $date->toDateString())
            ->count();
    }

    /**
     * Which of the given dates are full, as Y-m-d strings.
     *
     * @param  iterable<CarbonInterface|string>  $dates
     * @return list<string>
     */
    public function fullDatesAmong(iterable $dates): array
    {
        $dateStrings = collect($dates)
            ->map(fn (CarbonInterface|string $date): string => CarbonImmutable::parse($date)->toDateString())
            ->unique()
            ->values();

        if ($dateStrings->isEmpty()) {
            return [];
        }

        return Booking::query()
            ->approved()
            ->whereIn('event_date', $dateStrings->all())
            ->selectRaw('event_date, COUNT(*) as approved_count')
            ->groupBy('event_date')
            ->havingRaw('COUNT(*) >= ?', [$this->capacity()])
            ->pluck('event_date')
            ->map(fn (mixed $date): string => CarbonImmutable::parse($date)->toDateString())
            ->values()
            ->all();
    }

    /**
     * Whether a pending booking's date has already been filled by approved
     * bookings, so the admin should look before approving it.
     */
    public function hasDateConflict(Booking $booking): bool
    {
        return $booking->status === BookingStatus::Pending && $this->isFull($booking->event_date);
    }

    /**
     * Full dates in a calendar month, as Y-m-d strings in ascending order.
     *
     * @return list<string>
     */
    public function fullDatesInMonth(CarbonInterface $month): array
    {
        $start = CarbonImmutable::parse($month)->startOfMonth();

        return Booking::query()
            ->approved()
            ->whereBetween('event_date', [$start->toDateString(), $start->endOfMonth()->toDateString()])
            ->selectRaw('event_date, COUNT(*) as approved_count')
            ->groupBy('event_date')
            ->havingRaw('COUNT(*) >= ?', [$this->capacity()])
            ->orderBy('event_date')
            ->pluck('event_date')
            ->map(fn (mixed $date): string => CarbonImmutable::parse($date)->toDateString())
            ->values()
            ->all();
    }
}
