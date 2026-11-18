<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Month view of confirmed (and, optionally, pending) bookings.
     */
    public function __invoke(Request $request): View
    {
        Gate::authorize('viewAny', Booking::class);

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'pending' => ['nullable', 'boolean'],
        ]);

        $month = isset($validated['month'])
            ? CarbonImmutable::createFromFormat('!Y-m', $validated['month'])
            : CarbonImmutable::today()->startOfMonth();
        $showPending = (bool) ($validated['pending'] ?? true);

        $statuses = $showPending ? [BookingStatus::Approved, BookingStatus::Pending] : [BookingStatus::Approved];

        $bookingsByDate = Booking::query()
            ->whereBetween('event_date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()])
            ->whereIn('status', $statuses)
            ->orderBy('event_date')
            ->orderBy('status')
            ->get(['id', 'reference', 'customer_name', 'event_type', 'event_date', 'status', 'guest_count'])
            ->groupBy(fn (Booking $booking): string => $booking->event_date->toDateString());

        $gridStart = $month->startOfMonth()->startOfWeek(CarbonImmutable::MONDAY);
        $gridEnd = $month->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY);
        $days = collect();
        for ($day = $gridStart; $day <= $gridEnd; $day = $day->addDay()) {
            $days->push($day);
        }

        $previousMonth = $month->subMonth()->format('Y-m');
        $nextMonth = $month->addMonth()->format('Y-m');

        return view('admin.calendar', compact('month', 'days', 'bookingsByDate', 'showPending', 'previousMonth', 'nextMonth'));
    }
}
