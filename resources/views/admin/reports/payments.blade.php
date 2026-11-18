@php use App\Support\Money; @endphp

<x-app-layout>
    <x-slot:title>Payments report | Lekobosi</x-slot:title>

    <x-slot name="header">
        @include('admin.reports.partials.header', ['current' => 'payments'])
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-4 md:grid-cols-3">
            <section class="rounded-xl border border-saffron-400 bg-white p-5 ring-1 ring-saffron-300">
                <h2 class="text-sm text-stone-500">Received</h2>
                <p class="mt-2 font-display text-3xl font-semibold text-stone-900" data-testid="confirmed-total">{{ Money::format($report['confirmedTotal']) }}</p>
                <p class="mt-1 text-xs text-stone-500">Confirmed payments only</p>
            </section>
            <section class="rounded-xl border border-stone-200 bg-white p-5" aria-label="By method">
                <h2 class="text-sm font-semibold text-stone-500">By method</h2>
                <dl class="mt-3 space-y-1.5 text-sm">
                    @foreach ($report['byMethod'] as $method => $total)
                        <div class="flex justify-between"><dt class="text-stone-700">{{ $method }}</dt><dd class="font-semibold text-stone-900">{{ Money::format($total) }}</dd></div>
                    @endforeach
                </dl>
            </section>
            <section class="rounded-xl border border-stone-200 bg-white p-5">
                <h2 class="text-sm text-stone-500">Voided</h2>
                <p class="mt-2 font-display text-2xl font-semibold text-stone-900">{{ Money::format($report['voidedTotal']) }}</p>
                <p class="mt-1 text-xs text-stone-500">Recorded by mistake and voided; not counted above</p>
            </section>
        </div>

        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white" aria-label="Payments">
            @if ($report['payments']->isEmpty())
                <p class="px-6 py-14 text-center text-stone-500">No payments received in this date range.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-stone-50 text-stone-500">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">Receipt</th>
                                <th scope="col" class="px-4 py-3 font-medium">Received</th>
                                <th scope="col" class="px-4 py-3 font-medium">Customer</th>
                                <th scope="col" class="px-4 py-3 font-medium">Method</th>
                                <th scope="col" class="px-4 py-3 font-medium">Recorded by</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($report['payments'] as $payment)
                                @php $voided = $payment->status === \App\Enums\PaymentStatus::Voided; @endphp
                                <tr @class(['text-stone-400' => $voided])>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <a href="{{ route('admin.payments.receipt', $payment) }}" class="font-mono text-xs hover:text-brand-700">{{ $payment->receipt_number }}</a>
                                        @if ($voided) <span class="ml-1 rounded bg-brand-100 px-1.5 py-0.5 text-xs font-semibold text-brand-800">Voided</span> @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $payment->received_at->format('j M Y') }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.bookings.show', $payment->booking) }}" class="hover:text-brand-700">{{ $payment->booking->customer_name }}</a>
                                        <span class="block font-mono text-xs text-stone-500">{{ $payment->booking->reference }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ $payment->method->label() }}</td>
                                    <td class="px-4 py-3">{{ $payment->recorder?->name }}</td>
                                    <td @class(['whitespace-nowrap px-4 py-3 text-right font-medium', 'line-through' => $voided])>{{ Money::format($payment->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
