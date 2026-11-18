<x-app-layout>
    <x-slot:title>Bookings | Lekobosi</x-slot:title>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">Bookings</h1>
            <div class="flex flex-wrap gap-3">
                @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                    <a href="{{ route('admin.conflict-attempts') }}" class="btn-outline">Turned-away requests</a>
                @endif
                <a href="{{ route('admin.bookings.create') }}" class="btn-brand">New phone booking</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <form method="get" action="{{ route('admin.bookings.index') }}" class="grid gap-4 rounded-xl border border-stone-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-6" role="search">
            <div class="lg:col-span-2">
                <label for="q" class="field-label">Search</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Name, phone or reference" class="field-input">
            </div>
            <div>
                <label for="status" class="field-label">Status</label>
                <select id="status" name="status" class="field-input">
                    <option value="">Any status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="source" class="field-label">Source</label>
                <select id="source" name="source" class="field-input">
                    <option value="">Any source</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source->value }}" @selected(($filters['source'] ?? null) === $source->value)>{{ $source->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="from" class="field-label">Event from</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="field-input">
            </div>
            <div>
                <label for="to" class="field-label">Event to</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="field-input">
                @error('to') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-wrap items-end justify-between gap-3 sm:col-span-2 lg:col-span-6">
                <div class="flex items-center gap-2 text-sm">
                    <label for="sort" class="text-stone-600">Sort by</label>
                    <select id="sort" name="sort" class="rounded-lg border-stone-300 py-1.5 text-sm focus:border-brand-600 focus:ring-brand-600">
                        <option value="created_at" @selected(($filters['sort'] ?? 'created_at') === 'created_at')>Newest requests</option>
                        <option value="event_date" @selected(($filters['sort'] ?? null) === 'event_date')>Event date (soonest)</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.bookings.index') }}" class="btn-outline py-2.5">Clear</a>
                    <button type="submit" class="btn-brand py-2.5">Apply filters</button>
                </div>
            </div>
        </form>

        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white" aria-labelledby="results-heading">
            <h2 id="results-heading" class="border-b border-stone-200 px-6 py-4 text-sm text-stone-600">
                {{ number_format($bookings->total()) }} {{ \Illuminate\Support\Str::plural('booking', $bookings->total()) }}
            </h2>

            @if ($bookings->isEmpty())
                <div class="px-6 py-14 text-center">
                    <p class="font-medium text-stone-800">No bookings match these filters</p>
                    <a href="{{ route('admin.bookings.index') }}" class="mt-2 inline-block text-sm font-semibold text-brand-700">Clear the filters</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-stone-50 text-stone-500">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">Customer</th>
                                <th scope="col" class="px-6 py-3 font-medium">WhatsApp</th>
                                <th scope="col" class="px-6 py-3 font-medium">Event</th>
                                <th scope="col" class="px-6 py-3 font-medium">Event date</th>
                                <th scope="col" class="px-6 py-3 font-medium">Guests</th>
                                <th scope="col" class="px-6 py-3 font-medium">Status</th>
                                <th scope="col" class="px-6 py-3 font-medium"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($bookings as $booking)
                                <tr class="hover:bg-stone-50">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-stone-900">{{ $booking->customer_name }}</p>
                                        <p class="font-mono text-xs text-stone-500">{{ $booking->reference }} &middot; {{ $booking->source->label() }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">{{ $booking->whatsapp_number ?? $booking->customer_phone }}</td>
                                    <td class="px-6 py-4">{{ $booking->event_type->label() }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        {{ $booking->event_date->format('j M Y') }}
                                        @if ($booking->status === \App\Enums\BookingStatus::Pending && in_array($booking->event_date->toDateString(), $conflictDates, true))
                                            <span class="mt-1 block text-xs font-semibold text-brand-700">Date already taken</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ number_format($booking->guest_count) }}</td>
                                    <td class="px-6 py-4"><x-booking-status :status="$booking->status" class="text-xs" /></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-semibold text-brand-700 hover:text-brand-800">
                                            {{ $booking->status === \App\Enums\BookingStatus::Pending ? 'Review' : 'View' }}<span class="sr-only"> {{ $booking->reference }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($bookings->hasPages())
                    <div class="border-t border-stone-200 px-6 py-4">{{ $bookings->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-app-layout>
