@php use App\Support\Money; @endphp

<x-app-layout>
    <x-slot:title>Payments and Balances | Lekobosi</x-slot:title>

    <x-slot name="header">
        <h1 class="text-2xl font-semibold">Payments and balances</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <dl class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-xl border border-stone-200 bg-white p-5">
                <dt class="text-sm text-stone-500">Quoted (approved bookings)</dt>
                <dd class="mt-2 font-display text-2xl font-semibold text-stone-900">{{ Money::format($totals['quoted']) }}</dd>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-5">
                <dt class="text-sm text-stone-500">Collected</dt>
                <dd class="mt-2 font-display text-2xl font-semibold text-emerald-700">{{ Money::format($totals['collected']) }}</dd>
            </div>
            <div class="rounded-xl border border-saffron-400 bg-white p-5 ring-1 ring-saffron-300">
                <dt class="text-sm text-stone-500">Outstanding</dt>
                <dd class="mt-2 font-display text-2xl font-semibold text-stone-900" data-testid="total-outstanding">{{ Money::format($totals['outstanding']) }}</dd>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-5">
                <dt class="text-sm text-stone-500">Paid on cancelled bookings</dt>
                <dd class="mt-2 font-display text-2xl font-semibold text-stone-900">{{ Money::format($totals['held_on_cancelled']) }}</dd>
                <dd class="mt-1 text-xs text-stone-500">Settle refunds directly with the customer.</dd>
            </div>
        </dl>

        @if ($unquotedCount > 0)
            <p class="rounded-lg border border-saffron-300 bg-saffron-50 px-4 py-3 text-sm text-stone-800">
                {{ $unquotedCount }} approved {{ \Illuminate\Support\Str::plural('booking', $unquotedCount) }} {{ $unquotedCount === 1 ? 'has' : 'have' }} no quote yet, so {{ $unquotedCount === 1 ? "it isn't" : "they aren't" }} counted above.
            </p>
        @endif

        <form method="get" action="{{ route('admin.payments.index') }}" class="flex flex-wrap items-end gap-4 rounded-xl border border-stone-200 bg-white p-5">
            <div>
                <label for="balance" class="field-label">Payment status</label>
                <select id="balance" name="balance" class="field-input">
                    <option value="">Any</option>
                    @foreach ($balanceStatuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['balance'] ?? null) === $status->value)>{{ $status->label() }}</option>
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
            <label class="flex items-center gap-2 pb-3 text-sm text-stone-700">
                <input type="checkbox" name="outstanding" value="1" @checked($outstandingOnly) class="rounded border-stone-300 text-brand-700 focus:ring-brand-600">
                Outstanding only
            </label>
            <div class="ml-auto flex gap-3">
                <a href="{{ route('admin.payments.index') }}" class="btn-outline py-2.5">Clear</a>
                <button type="submit" class="btn-brand py-2.5">Apply</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white" aria-label="Approved bookings and balances">
            @if ($bookings->isEmpty())
                <p class="px-6 py-14 text-center text-stone-500">No approved bookings match these filters.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-stone-50 text-stone-500">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">Customer</th>
                                <th scope="col" class="px-6 py-3 font-medium">Event date</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium">Quote</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium">Paid</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium">Balance</th>
                                <th scope="col" class="px-6 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($bookings as $booking)
                                @php $status = $booking->balanceStatus(); @endphp
                                <tr class="hover:bg-stone-50">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.bookings.show', $booking) }}#payments" class="font-medium text-stone-900 hover:text-brand-700">{{ $booking->customer_name }}</a>
                                        <p class="font-mono text-xs text-stone-500">{{ $booking->reference }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">{{ $booking->event_date->format('j M Y') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">{{ $booking->quoted_amount === null ? 'No quote' : Money::format($booking->quoted_amount) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-emerald-700">{{ Money::format($booking->amountPaid()) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-semibold">{{ Money::format($booking->balance()) }}</td>
                                    <td class="px-6 py-4">
                                        @if ($status)
                                            <x-balance-status :status="$status" />
                                        @else
                                            <a href="{{ route('admin.bookings.show', $booking) }}#payments" class="text-xs font-semibold text-brand-700">Set quote</a>
                                        @endif
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
