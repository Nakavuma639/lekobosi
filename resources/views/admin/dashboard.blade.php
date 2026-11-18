<x-app-layout>
    <x-slot:title>Dashboard | Lekobosi</x-slot:title>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Welcome, {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ now()->format('l, j F Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.calendar') }}" class="btn-outline">Calendar</a>
                <a href="{{ route('admin.bookings.index') }}" class="btn-brand">All bookings</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
        <section aria-labelledby="totals-heading">
            <h2 id="totals-heading" class="sr-only">Booking totals</h2>
            <dl class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ([
                    ['label' => 'Total bookings', 'value' => $totals['total'], 'href' => route('admin.bookings.index')],
                    ['label' => 'Awaiting decision', 'value' => $totals['pending'], 'href' => route('admin.bookings.index', ['status' => 'pending']), 'highlight' => $totals['pending'] > 0],
                    ['label' => 'Approved', 'value' => $totals['approved'], 'href' => route('admin.bookings.index', ['status' => 'approved'])],
                    ['label' => 'Rejected', 'value' => $totals['rejected'], 'href' => route('admin.bookings.index', ['status' => 'rejected'])],
                    ['label' => 'Events in the next 30 days', 'value' => $totals['upcoming_30_days'], 'href' => route('admin.calendar')],
                    ['label' => 'Unread messages', 'value' => $unreadMessages, 'href' => route('admin.messages.index', ['unread' => 1]), 'highlight' => $unreadMessages > 0],
                    ['label' => 'Outstanding balance', 'value' => $outstandingBalance, 'href' => route('admin.payments.index', ['outstanding' => 1]), 'money' => true, 'wide' => true],
                ] as $tile)
                    <a href="{{ $tile['href'] }}" @class([
                        'rounded-xl border bg-white p-5 transition hover:border-brand-300',
                        'col-span-2' => $tile['wide'] ?? false,
                        'border-saffron-400 ring-1 ring-saffron-300' => $tile['highlight'] ?? false,
                        'border-stone-200' => ! ($tile['highlight'] ?? false),
                    ])>
                        <dt class="text-sm text-stone-500">{{ $tile['label'] }}</dt>
                        <dd class="mt-2 font-display text-3xl font-semibold text-stone-900" data-testid="total-{{ \Illuminate\Support\Str::slug($tile['label']) }}">{{ ($tile['money'] ?? false) ? \App\Support\Money::format($tile['value']) : number_format($tile['value']) }}</dd>
                    </a>
                @endforeach
            </dl>
        </section>

        <div class="grid gap-8 lg:grid-cols-3">
            <section class="rounded-xl border border-stone-200 bg-white lg:col-span-2" aria-labelledby="pending-heading">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-200 px-6 py-4">
                    <h2 id="pending-heading" class="text-lg font-semibold">Awaiting your decision</h2>
                    @if ($totals['pending'] > $pendingBookings->count())
                        <a href="{{ route('admin.bookings.index', ['status' => 'pending', 'sort' => 'event_date']) }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800">See all {{ $totals['pending'] }}</a>
                    @endif
                </div>

                @if ($pendingBookings->isEmpty())
                    <div class="px-6 py-14 text-center">
                        <p class="font-medium text-stone-800">You're all caught up</p>
                        <p class="mt-1 text-stone-500">New requests from the website will appear here.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-stone-50 text-stone-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Customer</th>
                                    <th scope="col" class="px-6 py-3 font-medium">WhatsApp</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Event</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Date</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Guests</th>
                                    <th scope="col" class="px-6 py-3 font-medium"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($pendingBookings as $booking)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-stone-900">{{ $booking->customer_name }}</p>
                                            <p class="font-mono text-xs text-stone-500">{{ $booking->reference }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $booking->whatsapp_number ?? $booking->customer_phone }}</td>
                                        <td class="px-6 py-4">{{ $booking->event_type->label() }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $booking->event_date->format('j M Y') }}
                                            @if (in_array($booking->event_date->toDateString(), $conflictDates, true))
                                                <span class="mt-1 block text-xs font-semibold text-brand-700">Date already taken</span>
                                            @elseif ($booking->event_date->lt(today()))
                                                <span class="mt-1 block text-xs font-semibold text-stone-500">Date has passed</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ number_format($booking->guest_count) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.bookings.show', $booking) }}" class="font-semibold text-brand-700 hover:text-brand-800">Review</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="upcoming-heading">
                <h2 id="upcoming-heading" class="border-b border-stone-200 px-6 py-4 text-lg font-semibold">Coming up</h2>
                @if ($upcomingBookings->isEmpty())
                    <p class="px-6 py-10 text-center text-stone-500">No approved events coming up.</p>
                @else
                    <ul class="divide-y divide-stone-100">
                        @foreach ($upcomingBookings as $booking)
                            <li>
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-stone-50">
                                    <div class="w-12 shrink-0 text-center">
                                        <p class="text-xs uppercase text-stone-500">{{ $booking->event_date->format('M') }}</p>
                                        <p class="font-display text-xl font-semibold text-stone-900">{{ $booking->event_date->format('j') }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-stone-900">{{ $booking->event_type->label() }}</p>
                                        <p class="truncate text-sm text-stone-500">{{ $booking->customer_name }} &middot; {{ number_format($booking->guest_count) }} guests</p>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
