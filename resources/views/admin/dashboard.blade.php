<x-app-layout>
    <x-slot:title>Dashboard | Lekobosi</x-slot:title>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Welcome, {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ now()->format('l, j F Y') }}</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="btn-brand">New booking</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
        <section aria-labelledby="totals-heading">
            <h2 id="totals-heading" class="sr-only">Totals</h2>
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-stone-200 bg-white p-6">
                    <dt class="text-sm text-stone-500">Registered customers</dt>
                    <dd class="mt-2 font-display text-3xl font-semibold text-stone-900">{{ number_format($customerCount) }}</dd>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white p-6">
                    <dt class="text-sm text-stone-500">Active staff accounts</dt>
                    <dd class="mt-2 font-display text-3xl font-semibold text-stone-900">{{ number_format($staffCount) }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="bookings-heading">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-200 px-6 py-4">
                <h2 id="bookings-heading" class="text-lg font-semibold">Booking requests</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-stone-50 text-stone-500">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">Customer</th>
                            <th scope="col" class="px-6 py-3 font-medium">WhatsApp</th>
                            <th scope="col" class="px-6 py-3 font-medium">Event</th>
                            <th scope="col" class="px-6 py-3 font-medium">Date</th>
                            <th scope="col" class="px-6 py-3 font-medium">Guests</th>
                            <th scope="col" class="px-6 py-3 font-medium">Status</th>
                            <th scope="col" class="px-6 py-3 font-medium"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <p class="font-medium text-stone-800">No booking requests yet</p>
                                <p class="mt-1 text-stone-500">Requests from the website will appear here for you to approve or reject.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="notifications-heading">
            <h2 id="notifications-heading" class="text-lg font-semibold">Notifications</h2>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-stone-600">
                You'll get an email when a new booking is submitted, and customers get an email when you approve or reject their request.
                Each booking will also have a WhatsApp button that opens a ready-written message to the customer.
                Automatic WhatsApp messages can be switched on once a WhatsApp Business account is set up.
            </p>
        </section>
    </div>
</x-app-layout>
