<x-app-layout>
    <x-slot:title>Calendar | Lekobosi</x-slot:title>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">{{ $month->format('F Y') }}</h1>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.calendar', ['month' => $previousMonth, 'pending' => (int) $showPending]) }}" class="btn-outline py-2" aria-label="Previous month">Previous</a>
                <a href="{{ route('admin.calendar', ['pending' => (int) $showPending]) }}" class="btn-outline py-2">This month</a>
                <a href="{{ route('admin.calendar', ['month' => $nextMonth, 'pending' => (int) $showPending]) }}" class="btn-outline py-2" aria-label="Next month">Next</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
            <div class="flex items-center gap-5 text-stone-600">
                <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-sm bg-emerald-600"></span> Approved</span>
                @if ($showPending)
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-sm border border-saffron-500 bg-saffron-100"></span> Pending</span>
                @endif
            </div>
            <a href="{{ route('admin.calendar', ['month' => $month->format('Y-m'), 'pending' => (int) ! $showPending]) }}" class="font-semibold text-brand-700 hover:text-brand-800">
                {{ $showPending ? 'Show approved only' : 'Show pending requests too' }}
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
            <div class="hidden grid-cols-7 border-b border-stone-200 bg-stone-50 text-center text-xs font-medium text-stone-500 md:grid">
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                    <div class="py-2">{{ $weekday }}</div>
                @endforeach
            </div>

            <ol class="grid grid-cols-1 md:grid-cols-7">
                @foreach ($days as $day)
                    @php
                        $dayBookings = $bookingsByDate->get($day->toDateString(), collect());
                        $inMonth = $day->month === $month->month;
                    @endphp
                    <li @class([
                        'min-h-28 border-b border-stone-100 p-2 md:border-r',
                        'bg-stone-50/60 text-stone-400 max-md:hidden' => ! $inMonth,
                        'max-md:hidden' => $inMonth && $dayBookings->isEmpty(),
                    ])>
                        <p @class([
                            'text-sm font-medium',
                            'inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-700 text-white' => $day->isToday(),
                        ])>
                            <span class="md:hidden">{{ $day->format('D j M') }}</span>
                            <span class="hidden md:inline">{{ $day->format('j') }}</span>
                        </p>
                        <ul class="mt-1 space-y-1">
                            @foreach ($dayBookings as $booking)
                                <li>
                                    <a href="{{ route('admin.bookings.show', $booking) }}" @class([
                                        'block truncate rounded px-2 py-1 text-xs font-medium',
                                        'bg-emerald-600 text-white hover:bg-emerald-700' => $booking->status === \App\Enums\BookingStatus::Approved,
                                        'border border-saffron-500 bg-saffron-50 text-stone-800 hover:bg-saffron-100' => $booking->status === \App\Enums\BookingStatus::Pending,
                                    ]) title="{{ $booking->customer_name }}, {{ $booking->event_type->label() }}, {{ $booking->guest_count }} guests">
                                        {{ $booking->event_type->label() }} &middot; {{ $booking->customer_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ol>
        </div>

        @if ($bookingsByDate->isEmpty())
            <p class="text-center text-stone-500">No {{ $showPending ? '' : 'approved ' }}bookings in {{ $month->format('F Y') }}.</p>
        @endif
    </div>
</x-app-layout>
