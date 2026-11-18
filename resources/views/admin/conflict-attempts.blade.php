<x-app-layout>
    <x-slot:title>Turned-away requests | Lekobosi</x-slot:title>

    <x-slot name="header">
        <a href="{{ route('admin.bookings.index') }}" class="text-sm text-stone-500 hover:text-brand-700">All bookings</a>
        <h1 class="mt-1 text-2xl font-semibold">Turned-away requests</h1>
        <p class="mt-1 text-sm text-stone-500">People who tried to book a date that was already full. Worth a call to offer another date.</p>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <form method="get" action="{{ route('admin.conflict-attempts') }}" class="flex flex-wrap items-end gap-4 rounded-xl border border-stone-200 bg-white p-5">
            <div>
                <label for="from" class="field-label">Requested date from</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="field-input">
            </div>
            <div>
                <label for="to" class="field-label">to</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="field-input">
                @error('to') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="ml-auto flex gap-3">
                <a href="{{ route('admin.conflict-attempts') }}" class="btn-outline py-2.5">Clear</a>
                <button type="submit" class="btn-brand py-2.5">Apply</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white" aria-label="Turned-away requests">
            @if ($attempts->isEmpty())
                <p class="px-6 py-14 text-center text-stone-500">No requests have been turned away{{ ($filters['from'] ?? $filters['to'] ?? null) ? ' in this range' : '' }}.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-stone-50 text-stone-500">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">Customer</th>
                                <th scope="col" class="px-6 py-3 font-medium">Wanted</th>
                                <th scope="col" class="px-6 py-3 font-medium">Asked on</th>
                                <th scope="col" class="px-6 py-3 font-medium">Contact</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($attempts as $attempt)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-stone-900">{{ $attempt->customer_name }}</td>
                                    <td class="px-6 py-4">{{ $attempt->event_type->label() }}, {{ $attempt->requested_date->format('j M Y') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-stone-500">{{ $attempt->created_at->format('j M Y, g:i a') }}</td>
                                    <td class="px-6 py-4">
                                        <a href="tel:{{ $attempt->customer_phone }}" class="text-brand-700 hover:text-brand-800">{{ $attempt->customer_phone }}</a>
                                        &middot; <a href="https://wa.me/{{ ltrim($attempt->customer_phone, '+') }}" target="_blank" rel="noopener" class="text-brand-700 hover:text-brand-800">WhatsApp</a>
                                        @if ($attempt->customer_email)
                                            <a href="mailto:{{ $attempt->customer_email }}" class="block break-all text-xs text-stone-500 hover:text-brand-700">{{ $attempt->customer_email }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($attempts->hasPages())
                    <div class="border-t border-stone-200 px-6 py-4">{{ $attempts->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-app-layout>
