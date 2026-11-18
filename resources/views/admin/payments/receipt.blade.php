@php
    use App\Support\Money;
    $isVoided = $payment->status === \App\Enums\PaymentStatus::Voided;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_number }} | Lekobosi</title>
    @include('layouts.partials.fonts')
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A5 portrait; margin: 12mm; }
        @media print {
            body { background: #fff !important; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none !important; border: 0 !important; margin: 0 !important; max-width: none !important; }
        }
    </style>
</head>
<body class="bg-stone-100 font-sans text-stone-800 antialiased">
    <div class="no-print mx-auto flex max-w-xl items-center justify-between px-4 py-6">
        <a href="{{ route('admin.bookings.show', $booking) }}" class="text-sm text-stone-600 hover:text-brand-700">Back to booking {{ $booking->reference }}</a>
        <button type="button" onclick="window.print()" class="btn-brand py-2">Print receipt</button>
    </div>

    <main class="receipt relative mx-auto mb-10 max-w-xl overflow-hidden rounded-xl border border-stone-200 bg-white p-8 shadow-sm">
        @if ($isVoided)
            <p class="pointer-events-none absolute inset-0 flex items-center justify-center font-display text-8xl font-bold text-brand-700/15 -rotate-12" aria-hidden="true">VOID</p>
        @endif

        <header class="flex items-start justify-between gap-6 border-b border-stone-200 pb-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.jpeg') }}" alt="" class="h-16 w-16 object-contain">
                <div>
                    <p class="font-display text-lg font-semibold text-brand-700">Lekobosi</p>
                    <p class="text-xs leading-relaxed text-stone-500">Restaurant and Outside Catering Services<br>Kyankwanzi, Uganda &middot; 0775 847 855</p>
                </div>
            </div>
            <div class="text-right">
                <h1 class="text-xl font-bold">Receipt</h1>
                <p class="font-mono text-sm" data-testid="receipt-number">{{ $payment->receipt_number }}</p>
            </div>
        </header>

        @if ($isVoided)
            <p class="mt-6 rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-800">
                This receipt was voided on {{ $payment->voided_at?->format('j M Y') }}{{ $payment->voider ? ' by '.$payment->voider->name : '' }}: {{ $payment->void_reason }}
            </p>
        @endif

        <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-stone-500">Received from</dt>
                <dd class="font-medium text-stone-900">{{ $booking->customer_name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Date received</dt>
                <dd class="font-medium text-stone-900">{{ $payment->received_at->format('j F Y, g:i a') }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Booking reference</dt>
                <dd class="font-mono font-medium text-stone-900">{{ $booking->reference }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Event</dt>
                <dd class="font-medium text-stone-900">{{ $booking->event_type->label() }}, {{ $booking->event_date->format('j M Y') }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Payment method</dt>
                <dd class="font-medium text-stone-900">{{ $payment->method->label() }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Transaction reference</dt>
                <dd class="font-mono font-medium text-stone-900">{{ $payment->reference ?? '—' }}</dd>
            </div>
            @if ($payment->note)
                <div class="col-span-2">
                    <dt class="text-stone-500">Note</dt>
                    <dd class="font-medium text-stone-900">{{ $payment->note }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-8 rounded-lg bg-stone-50 p-5">
            <div class="flex items-baseline justify-between">
                <span class="text-sm text-stone-600">Amount received</span>
                <span @class(['font-display text-3xl font-bold text-stone-900', 'line-through' => $isVoided]) data-testid="receipt-amount">{{ Money::format($payment->amount) }}</span>
            </div>
            <dl class="mt-4 space-y-1 border-t border-stone-200 pt-4 text-sm">
                <div class="flex justify-between"><dt class="text-stone-600">Agreed quote</dt><dd>{{ Money::format($booking->quoted_amount) }}</dd></div>
                <div class="flex justify-between"><dt class="text-stone-600">Total paid to date</dt><dd>{{ Money::format($paidUpToThisReceipt) }}</dd></div>
                <div class="flex justify-between font-semibold"><dt>Balance remaining</dt><dd data-testid="receipt-balance">{{ Money::format($balanceAfterThisReceipt) }}</dd></div>
            </dl>
            <p class="mt-2 text-xs text-stone-500">Totals as at this receipt.</p>
        </div>

        <footer class="mt-8 flex items-end justify-between gap-6 text-xs text-stone-500">
            <p>Received by {{ $payment->recorder?->name }}</p>
            <p class="text-right">Thank you for choosing Lekobosi.<br>For excellent service.</p>
        </footer>
    </main>
</body>
</html>
