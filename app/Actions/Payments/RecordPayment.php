<?php

namespace App\Actions\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\NotificationDispatcher;
use App\Support\ReceiptNumber;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class RecordPayment
{
    private const MAX_RECEIPT_ATTEMPTS = 5;

    public function __construct(private NotificationDispatcher $notifications) {}

    /**
     * Record money received against an approved, quoted booking.
     *
     * The booking row is locked first, so two staff recording at the same time
     * can't together push the amount paid above the quote.
     *
     * @throws ValidationException when the booking can't take payments or the amount is too high.
     */
    public function handle(
        Booking $booking,
        int $amount,
        PaymentMethod $method,
        CarbonImmutable $receivedAt,
        User $recordedBy,
        ?string $reference = null,
        ?string $note = null,
    ): Payment {
        $amountPaidToDate = 0;
        $balanceRemaining = 0;

        $payment = DB::transaction(function () use ($booking, $amount, $method, $receivedAt, $recordedBy, $reference, $note, &$amountPaidToDate, &$balanceRemaining): Payment {
            /** @var Booking $booking */
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (! $booking->acceptsPayments()) {
                throw ValidationException::withMessages([
                    'amount' => 'Payments can only be recorded on an approved booking with a quote.',
                ]);
            }

            $balance = $booking->quoted_amount - (int) $booking->payments()->confirmed()->sum('amount');

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'That is more than the balance of UGX '.number_format(max($balance, 0)).'. Check the amount, or raise the quote first.',
                ]);
            }

            $payment = new Payment;
            $payment->booking_id = $booking->id;
            $payment->amount = $amount;
            $payment->method = $method;
            $payment->reference = $reference;
            $payment->received_at = $receivedAt;
            $payment->recorded_by = $recordedBy->id;
            $payment->note = $note;
            $payment->status = PaymentStatus::Confirmed;

            $this->saveWithUniqueReceiptNumber($payment);

            $amountPaidToDate = $booking->quoted_amount - $balance + $amount;
            $balanceRemaining = $balance - $amount;

            return $payment;
        });

        $this->notifications->paymentRecorded($payment, $amountPaidToDate, $balanceRemaining);

        return $payment;
    }

    /**
     * Save the payment with the next receipt number, retrying on a collision.
     */
    private function saveWithUniqueReceiptNumber(Payment $payment): void
    {
        for ($attempt = 1; $attempt <= self::MAX_RECEIPT_ATTEMPTS; $attempt++) {
            $payment->receipt_number = ReceiptNumber::next(now());

            try {
                DB::transaction(fn () => $payment->save());

                return;
            } catch (UniqueConstraintViolationException) {
                // Another payment took this number a moment ago: try the next one.
            }
        }

        throw new RuntimeException('Could not allocate a receipt number.');
    }
}
