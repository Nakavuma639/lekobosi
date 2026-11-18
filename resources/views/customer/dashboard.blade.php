<x-app-layout>
    <x-slot:title>My bookings | Lekobosi</x-slot:title>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Hello, {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">Your bookings, payments and balances live here.</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="btn-brand">Request a booking</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div class="space-y-6 lg:col-span-2">
            @if (session('status'))
                <p class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
            @endif

            <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="my-bookings-heading">
                <h2 id="my-bookings-heading" class="border-b border-stone-200 px-6 py-4 text-lg font-semibold">My bookings</h2>
                @if ($bookings->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <p class="font-medium text-stone-800">You haven't made a booking yet</p>
                        <p class="mx-auto mt-1 max-w-sm text-stone-500">Bookings you make while logged in appear here with their status, quote and balance.</p>
                        <a href="{{ route('bookings.create') }}" class="btn-brand mt-6">Request a booking</a>
                    </div>
                @else
                    <ul class="divide-y divide-stone-200">
                        @foreach ($bookings as $booking)
                            <li>
                                <a href="{{ route('customer.bookings.show', $booking) }}" class="flex flex-wrap items-center justify-between gap-4 px-6 py-5 hover:bg-stone-50">
                                    <div>
                                        <p class="font-medium text-stone-900">{{ $booking->event_type->label() }} &middot; {{ $booking->event_date->format('j M Y') }}</p>
                                        <p class="mt-1 text-sm text-stone-500">
                                            <span class="font-mono tracking-wide">{{ $booking->reference }}</span>
                                            &middot; {{ $booking->event_location }} &middot; {{ number_format($booking->guest_count) }} guests
                                        </p>
                                        @if ($booking->quoted_amount !== null)
                                            <p class="mt-1 text-sm text-stone-700">
                                                Paid {{ \App\Support\Money::format($booking->amountPaid()) }} of {{ \App\Support\Money::format($booking->quoted_amount) }}
                                                &middot; <span class="font-semibold">Balance {{ \App\Support\Money::format($booking->balance()) }}</span>
                                            </p>
                                        @endif
                                    </div>
                                    <x-booking-status :status="$booking->status" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($bookings->hasPages())
                        <div class="border-t border-stone-200 px-6 py-4">{{ $bookings->links() }}</div>
                    @endif
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="account-heading">
                <h2 id="account-heading" class="text-lg font-semibold">Your details</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-stone-500">Email</dt>
                        <dd class="break-all font-medium text-stone-800">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Phone</dt>
                        <dd class="font-medium text-stone-800">{{ $user->phone ?? 'Not added yet' }}</dd>
                    </div>
                </dl>
                <a href="{{ route('profile.edit') }}" class="mt-5 inline-block text-sm font-semibold text-brand-700 hover:text-brand-800">Edit your details</a>
            </section>

            <section class="rounded-xl bg-brand-700 p-6 text-white" aria-labelledby="help-heading">
                <h2 id="help-heading" class="text-lg font-semibold text-white">Have a booking reference?</h2>
                <p class="mt-2 text-sm text-brand-100">Check the status of any request, including ones you made without logging in.</p>
                <a href="{{ route('bookings.track') }}" class="btn-primary mt-5">Track a booking</a>
            </section>
        </aside>
    </div>
</x-app-layout>
