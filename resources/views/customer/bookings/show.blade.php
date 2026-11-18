@php use App\Support\Money; @endphp

<x-app-layout>
    <x-slot:title>Booking {{ $booking->reference }} | Lekobosi</x-slot:title>

    <x-slot name="header">
        <a href="{{ route('customer.dashboard') }}" class="text-sm text-stone-500 hover:text-brand-700">My bookings</a>
        <h1 class="mt-1 flex flex-wrap items-center gap-3 text-2xl font-semibold">
            {{ $booking->event_type->label() }}, {{ $booking->event_date->format('j F Y') }}
            <x-booking-status :status="$booking->status" />
        </h1>
        <p class="mt-1 font-mono text-sm text-stone-500">{{ $booking->reference }}</p>
    </x-slot>

    <div class="mx-auto grid max-w-5xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="balance-heading">
                <div class="flex items-center justify-between border-b border-stone-200 px-6 py-4">
                    <h2 id="balance-heading" class="text-lg font-semibold">Payments</h2>
                    @if ($booking->balanceStatus())
                        <x-balance-status :status="$booking->balanceStatus()" />
                    @endif
                </div>

                @if ($booking->quoted_amount === null)
                    <p class="px-6 py-8 text-stone-600">
                        @if ($booking->status === \App\Enums\BookingStatus::Pending)
                            Once your booking is approved we'll agree the price with you, and your payments will show here.
                        @else
                            No price has been agreed for this booking yet. We'll be in touch.
                        @endif
                    </p>
                @else
                    <dl class="grid grid-cols-3 divide-x divide-stone-100 text-center">
                        <div class="px-4 py-5">
                            <dt class="text-xs text-stone-500">Agreed price</dt>
                            <dd class="mt-1 font-display text-lg font-semibold text-stone-900">{{ Money::format($booking->quoted_amount) }}</dd>
                        </div>
                        <div class="px-4 py-5">
                            <dt class="text-xs text-stone-500">You've paid</dt>
                            <dd class="mt-1 font-display text-lg font-semibold text-emerald-700">{{ Money::format($booking->amountPaid()) }}</dd>
                        </div>
                        <div class="px-4 py-5">
                            <dt class="text-xs text-stone-500">Balance</dt>
                            <dd class="mt-1 font-display text-lg font-semibold text-stone-900" data-testid="customer-balance">{{ Money::format($booking->balance()) }}</dd>
                        </div>
                    </dl>

                    @if ($booking->payments->isNotEmpty())
                        <ul class="divide-y divide-stone-100 border-t border-stone-100">
                            @foreach ($booking->payments as $payment)
                                <li class="flex items-center justify-between gap-4 px-6 py-3 text-sm">
                                    <div>
                                        <p class="font-medium text-stone-900">{{ $payment->received_at->format('j M Y') }} &middot; {{ $payment->method->label() }}</p>
                                        <p class="font-mono text-xs text-stone-500">Receipt {{ $payment->receipt_number }}</p>
                                    </div>
                                    <span class="font-semibold">{{ Money::format($payment->amount) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="details-heading">
                <h2 id="details-heading" class="text-lg font-semibold">Booking details</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-stone-500">Location</dt><dd class="font-medium text-stone-900">{{ $booking->event_location }}</dd></div>
                    <div><dt class="text-stone-500">Guests</dt><dd class="font-medium text-stone-900">{{ number_format($booking->guest_count) }}</dd></div>
                    @if ($booking->status === \App\Enums\BookingStatus::Rejected && $booking->decision_note)
                        <div class="sm:col-span-2"><dt class="text-stone-500">Reason given</dt><dd class="font-medium text-stone-900">{{ $booking->decision_note }}</dd></div>
                    @endif
                </dl>
            </section>
        </div>

        <aside>
            <section class="rounded-xl bg-brand-700 p-6 text-white">
                <h2 class="text-lg font-semibold text-white">Paying a balance?</h2>
                <p class="mt-2 text-sm text-brand-100">Pay by cash or mobile money and quote your booking reference. We'll send you a receipt by email.</p>
                <a href="https://wa.me/256775847855" target="_blank" rel="noopener" class="btn-primary mt-5">Ask us on WhatsApp</a>
            </section>
        </aside>
    </div>
</x-app-layout>
