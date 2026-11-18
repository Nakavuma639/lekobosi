<?php

namespace App\Actions\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoidPayment
{
    /**
     * Void a payment recorded by mistake. The row is kept for the audit trail
     * and simply stops counting towards the balance.
     *
     * @throws ValidationException when the payment is already voided or no reason is given.
     */
    public function handle(Payment $payment, User $voidedBy, string $reason): Payment
    {
        $reason = trim(strip_tags($reason));

        if ($reason === '') {
            throw ValidationException::withMessages(['void_reason' => 'Give a reason for voiding this payment.']);
        }

        return DB::transaction(function () use ($payment, $voidedBy, $reason): Payment {
            /** @var Payment $payment */
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === PaymentStatus::Voided) {
                throw ValidationException::withMessages(['void_reason' => "Receipt {$payment->receipt_number} is already voided."]);
            }

            $payment->status = PaymentStatus::Voided;
            $payment->voided_by = $voidedBy->id;
            $payment->voided_at = now();
            $payment->void_reason = $reason;
            $payment->save();

            return $payment;
        });
    }
}
