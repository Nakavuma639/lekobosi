<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Payments\RecordPayment;
use App\Actions\Payments\VoidPayment;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\VoidPaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Record money received against a booking.
     */
    public function store(StorePaymentRequest $request, Booking $booking, RecordPayment $recordPayment): RedirectResponse
    {
        $payment = $recordPayment->handle(
            booking: $booking,
            amount: (int) $request->validated('amount'),
            method: PaymentMethod::from($request->validated('method')),
            receivedAt: CarbonImmutable::parse($request->validated('received_at')),
            recordedBy: $request->user(),
            reference: $request->validated('reference'),
            note: $request->validated('note'),
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', Money::format($payment->amount)." recorded on receipt {$payment->receipt_number}.")
            ->with('new_receipt', $payment->receipt_number);
    }

    /**
     * Void a payment recorded by mistake (owner only).
     */
    public function void(VoidPaymentRequest $request, Payment $payment, VoidPayment $voidPayment): RedirectResponse
    {
        $payment = $voidPayment->handle($payment, $request->user(), $request->validated('void_reason'));

        return redirect()
            ->route('admin.bookings.show', $payment->booking)
            ->with('status', "Receipt {$payment->receipt_number} has been voided.");
    }

    /**
     * Printable receipt, with the booking's balance just after this payment.
     */
    public function receipt(Payment $payment): View
    {
        Gate::authorize('view', $payment);

        $payment->load(['booking', 'recorder', 'voider']);
        $booking = $payment->booking;

        $paidUpToThisReceipt = (int) $booking->payments()
            ->confirmed()
            ->where('id', '<=', $payment->id)
            ->sum('amount');
        $balanceAfterThisReceipt = $booking->quoted_amount === null ? null : $booking->quoted_amount - $paidUpToThisReceipt;

        return view('admin.payments.receipt', compact('payment', 'booking', 'paidUpToThisReceipt', 'balanceAfterThisReceipt'));
    }
}
