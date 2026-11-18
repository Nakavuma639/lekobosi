<x-site-layout title="Booking request received | Lekobosi">
    <section class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100">
            <svg class="h-7 w-7 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>

        <h1 class="mt-6 text-4xl font-bold">We've received your booking request</h1>
        <p class="mt-4 text-lg leading-relaxed text-stone-600">
            Thank you, {{ $booking->customer_name }}. The owner will review your request and let you know the decision.
        </p>

        <div class="mt-10 rounded-2xl border-2 border-dashed border-saffron-400 bg-saffron-50 p-8">
            <p class="text-sm font-medium text-stone-600">Your booking reference</p>
            <p class="mt-2 select-all font-display text-4xl font-bold tracking-wider text-stone-900 sm:text-5xl" data-testid="booking-reference">{{ $booking->reference }}</p>
            <p class="mt-3 text-sm text-stone-600">Write it down or take a screenshot. You need it to check your booking status.</p>
        </div>

        <dl class="mt-10 grid gap-6 border-y border-stone-200 py-8 sm:grid-cols-3">
            <div>
                <dt class="text-sm text-stone-500">Event</dt>
                <dd class="mt-1 font-medium text-stone-900">{{ $booking->event_type->label() }}</dd>
            </div>
            <div>
                <dt class="text-sm text-stone-500">Date</dt>
                <dd class="mt-1 font-medium text-stone-900">{{ $booking->event_date->format('l, j F Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-stone-500">Status</dt>
                <dd class="mt-1"><x-booking-status :status="$booking->status" /></dd>
            </div>
        </dl>

        <p class="mt-8 leading-relaxed text-stone-600">
            Your date is not held until the booking is approved. We'll contact you on
            <span class="font-medium text-stone-900">{{ $booking->whatsapp_number }}</span> to agree the menu and quote.
        </p>

        <div class="mt-10 flex flex-wrap gap-4">
            <a href="{{ route('bookings.track', ['reference' => $booking->reference]) }}" class="btn-brand">Track this booking</a>
            <a href="{{ route('home') }}" class="btn-outline">Back to the home page</a>
        </div>
    </section>
</x-site-layout>
