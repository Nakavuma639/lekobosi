@php
    use App\Enums\BookingStatus;
    use App\Enums\PaymentStatus;
    use App\Support\Money;

    $balanceStatus = $booking->balanceStatus();
    $canEditQuote = $booking->status !== BookingStatus::Rejected && auth()->user()->can('updateQuote', $booking);
    $canRecord = $booking->acceptsPayments() && $booking->balance() > 0 && auth()->user()->can('recordPayment', $booking);
@endphp

<section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="payments-heading" id="payments">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 px-6 py-4">
        <h2 id="payments-heading" class="text-lg font-semibold">Quote and payments</h2>
        @if ($balanceStatus)
            <x-balance-status :status="$balanceStatus" />
        @endif
    </div>

    <dl class="grid grid-cols-3 divide-x divide-stone-100 border-b border-stone-100 text-center">
        <div class="px-4 py-5">
            <dt class="text-xs text-stone-500">Quote</dt>
            <dd class="mt-1 font-display text-lg font-semibold text-stone-900" data-testid="quote">{{ Money::format($booking->quoted_amount) }}</dd>
        </div>
        <div class="px-4 py-5">
            <dt class="text-xs text-stone-500">Paid</dt>
            <dd class="mt-1 font-display text-lg font-semibold text-emerald-700" data-testid="amount-paid">{{ Money::format($booking->amountPaid()) }}</dd>
        </div>
        <div class="px-4 py-5">
            <dt class="text-xs text-stone-500">Balance</dt>
            <dd class="mt-1 font-display text-lg font-semibold text-stone-900" data-testid="balance">{{ Money::format($booking->balance()) }}</dd>
        </div>
    </dl>

    @if ($booking->status === BookingStatus::Rejected && $booking->amountPaid() > 0)
        <p class="mx-6 mt-5 rounded-lg bg-saffron-50 px-4 py-3 text-sm text-stone-800">
            This booking was cancelled with {{ Money::format($booking->amountPaid()) }} paid. The payments stay on record; settle any refund with the customer directly.
        </p>
    @endif

    @if ($canEditQuote)
        <form method="POST" action="{{ route('admin.bookings.quote.update', $booking) }}" class="flex flex-wrap items-end gap-3 px-6 pt-5">
            @csrf
            @method('PATCH')
            <div class="min-w-48 flex-1">
                <label for="quote_amount" class="field-label">{{ $booking->quoted_amount === null ? 'Set the quote (UGX)' : 'Change the quote (UGX)' }}</label>
                <input id="quote_amount" name="quoted_amount" type="text" inputmode="numeric" required
                       value="{{ old('quoted_amount', $booking->quoted_amount === null ? '' : number_format($booking->quoted_amount)) }}"
                       placeholder="e.g. 4,500,000" class="field-input">
                @error('quoted_amount') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-outline py-2.5">Save quote</button>
        </form>
    @endif

    <div class="px-6 py-5">
        @if ($booking->payments->isEmpty())
            <p class="text-sm text-stone-500">No payments recorded yet.</p>
        @else
            <div class="-mx-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-stone-50 text-stone-500">
                        <tr>
                            <th scope="col" class="px-6 py-2 font-medium">Receipt</th>
                            <th scope="col" class="px-6 py-2 font-medium">Received</th>
                            <th scope="col" class="px-6 py-2 font-medium">Method</th>
                            <th scope="col" class="px-6 py-2 text-right font-medium">Amount</th>
                            <th scope="col" class="px-6 py-2 font-medium"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($booking->payments as $payment)
                            <tr @class(['bg-stone-50 text-stone-400' => $payment->status === PaymentStatus::Voided])>
                                <td class="whitespace-nowrap px-6 py-3 align-top">
                                    <span class="font-mono text-xs">{{ $payment->receipt_number }}</span>
                                    @if ($payment->status === PaymentStatus::Voided)
                                        <span class="ml-1 rounded bg-brand-100 px-1.5 py-0.5 text-xs font-semibold text-brand-800">Voided</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-3 align-top">
                                    {{ $payment->received_at->format('j M Y') }}
                                    <span class="block text-xs text-stone-500">by {{ $payment->recorder?->name }}</span>
                                </td>
                                <td class="px-6 py-3 align-top">
                                    {{ $payment->method->label() }}
                                    @if ($payment->reference)
                                        <span class="block font-mono text-xs text-stone-500">{{ $payment->reference }}</span>
                                    @endif
                                    @if ($payment->note)
                                        <span class="block text-xs text-stone-500">{{ $payment->note }}</span>
                                    @endif
                                    @if ($payment->status === PaymentStatus::Voided)
                                        <span class="block text-xs text-brand-700">Voided by {{ $payment->voider?->name }}: {{ $payment->void_reason }}</span>
                                    @endif
                                </td>
                                <td @class(['whitespace-nowrap px-6 py-3 text-right align-top font-medium', 'line-through' => $payment->status === PaymentStatus::Voided])>{{ Money::format($payment->amount) }}</td>
                                <td class="whitespace-nowrap px-6 py-3 text-right align-top">
                                    <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="font-semibold text-brand-700 hover:text-brand-800">Receipt</a>
                                    @can('void', $payment)
                                        <details class="mt-1 text-left" @if ($errors->has('void_reason') && old('voiding') === $payment->receipt_number) open @endif>
                                            <summary class="cursor-pointer text-xs text-stone-500 hover:text-brand-700">Void</summary>
                                            <form method="POST" action="{{ route('admin.payments.void', $payment) }}" class="mt-2 w-56 space-y-2">
                                                @csrf
                                                <input type="hidden" name="voiding" value="{{ $payment->receipt_number }}">
                                                <label for="void_reason_{{ $payment->id }}" class="sr-only">Reason for voiding</label>
                                                <input id="void_reason_{{ $payment->id }}" name="void_reason" type="text" required maxlength="500" placeholder="Reason, e.g. entered twice" class="field-input py-1.5 text-sm">
                                                @if (old('voiding') === $payment->receipt_number)
                                                    @error('void_reason') <p class="field-error">{{ $message }}</p> @enderror
                                                @endif
                                                <button type="submit" class="btn-outline w-full border-brand-300 py-1.5 text-xs text-brand-800">Void this payment</button>
                                            </form>
                                        </details>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($canRecord)
        <form method="POST" action="{{ route('admin.bookings.payments.store', $booking) }}" class="space-y-4 border-t border-stone-200 px-6 py-5" x-data="{ method: '{{ old('method', 'cash') }}' }">
            @csrf
            <h3 class="font-semibold">Record a payment</h3>
            @error('amount')
                <p class="rounded-lg border border-brand-200 bg-brand-50 px-4 py-2 text-sm text-brand-800" role="alert">{{ $message }}</p>
            @enderror
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="payment_amount" class="field-label">Amount (UGX)</label>
                    <input id="payment_amount" name="amount" type="text" inputmode="numeric" required value="{{ old('amount') }}" placeholder="Balance is {{ number_format($booking->balance()) }}" class="field-input">
                </div>
                <div>
                    <label for="payment_method" class="field-label">Method</label>
                    <select id="payment_method" name="method" x-model="method" class="field-input">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->value }}" @selected(old('method', 'cash') === $method->value)>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="payment_reference" class="field-label">
                        Transaction ID / reference
                        <span class="font-normal text-stone-500" x-show="method === 'cash' || method === 'other'">(optional)</span>
                    </label>
                    <input id="payment_reference" name="reference" type="text" maxlength="100" value="{{ old('reference') }}" class="field-input">
                    @error('reference') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="payment_received_at" class="field-label">Received on</label>
                    <input id="payment_received_at" name="received_at" type="datetime-local" required value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" class="field-input">
                    @error('received_at') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="payment_note" class="field-label">Note <span class="font-normal text-stone-500">(optional)</span></label>
                <input id="payment_note" name="note" type="text" maxlength="255" value="{{ old('note') }}" placeholder="e.g. Deposit" class="field-input">
            </div>
            <button type="submit" class="btn-brand">Record payment</button>
        </form>
    @elseif ($booking->status === BookingStatus::Approved && $booking->quoted_amount === null)
        <p class="border-t border-stone-200 px-6 py-5 text-sm text-stone-600">Set the quote before recording payments.</p>
    @elseif ($booking->status === BookingStatus::Pending)
        <p class="border-t border-stone-200 px-6 py-5 text-sm text-stone-600">Payments can be recorded once the booking is approved.</p>
    @endif
</section>
