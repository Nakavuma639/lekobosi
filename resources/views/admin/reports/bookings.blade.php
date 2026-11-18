@php use App\Support\Money; @endphp

<x-app-layout>
    <x-slot:title>Bookings report | Lekobosi</x-slot:title>

    <x-slot name="header">
        @include('admin.reports.partials.header', ['current' => 'bookings'])
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-4 md:grid-cols-3">
            @foreach (['By status' => $report['byStatus'], 'By event type' => $report['byEventType'], 'By source' => $report['bySource']] as $heading => $counts)
                <section class="rounded-xl border border-stone-200 bg-white p-5" aria-label="{{ $heading }}">
                    <h2 class="text-sm font-semibold text-stone-500">{{ $heading }}</h2>
                    <dl class="mt-3 space-y-1.5 text-sm">
                        @foreach ($counts as $label => $count)
                            <div class="flex justify-between"><dt class="text-stone-700">{{ $label }}</dt><dd class="font-semibold text-stone-900">{{ $count }}</dd></div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>

        <p class="text-sm text-stone-600">
            <span class="font-semibold text-stone-900">{{ $report['bookings']->count() }}</span> {{ \Illuminate\Support\Str::plural('booking', $report['bookings']->count()) }}
            &middot; <span class="font-semibold text-stone-900">{{ number_format($report['guests']) }}</span> guests at approved events
        </p>

        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white" aria-label="Bookings">
            @if ($report['bookings']->isEmpty())
                <p class="px-6 py-14 text-center text-stone-500">No events in this date range.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-stone-50 text-stone-500">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">Date</th>
                                <th scope="col" class="px-4 py-3 font-medium">Customer</th>
                                <th scope="col" class="px-4 py-3 font-medium">Event</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium">Guests</th>
                                <th scope="col" class="px-4 py-3 font-medium">Status</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium">Quote</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($report['bookings'] as $booking)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $booking->event_date->format('j M Y') }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-medium text-stone-900 hover:text-brand-700">{{ $booking->customer_name }}</a>
                                        <span class="block font-mono text-xs text-stone-500">{{ $booking->reference }} &middot; {{ $booking->source->label() }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ $booking->event_type->label() }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($booking->guest_count) }}</td>
                                    <td class="px-4 py-3"><x-booking-status :status="$booking->status" class="text-xs" /></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">{{ Money::format($booking->quoted_amount) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">{{ Money::format($booking->balance()) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
