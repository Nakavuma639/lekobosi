<?php

use App\Enums\BalanceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentRecorded;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->travelTo('2026-11-18 10:00:00');
    $this->admin = User::factory()->admin()->create(['name' => 'Owner Harriet']);
    $this->staff = User::factory()->staff()->create(['name' => 'Staff Joseph']);
    $this->booking = Booking::factory()->approved()->create(['quoted_amount' => 4_000_000, 'customer_email' => 'amina@example.com']);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function paymentForm(array $overrides = []): array
{
    return array_merge([
        'amount' => '1,000,000',
        'method' => 'mtn_momo',
        'reference' => 'MP261118.1000.A12345',
        'received_at' => '2026-11-18 09:30',
        'note' => 'Deposit',
    ], $overrides);
}

describe('recording payments', function () {
    it('records a payment with a sequential receipt number', function () {
        $this->actingAs($this->staff)
            ->post(route('admin.bookings.payments.store', $this->booking), paymentForm())
            ->assertRedirect(route('admin.bookings.show', $this->booking))
            ->assertSessionHas('status', 'UGX 1,000,000 recorded on receipt RCT-2026-00001.');

        $payment = Payment::query()->sole();

        expect($payment->receipt_number)->toBe('RCT-2026-00001')
            ->and($payment->amount)->toBe(1_000_000)
            ->and($payment->method)->toBe(PaymentMethod::MtnMomo)
            ->and($payment->reference)->toBe('MP261118.1000.A12345')
            ->and($payment->received_at->format('Y-m-d H:i'))->toBe('2026-11-18 09:30')
            ->and($payment->recorded_by)->toBe($this->staff->id)
            ->and($payment->note)->toBe('Deposit')
            ->and($payment->status)->toBe(PaymentStatus::Confirmed);
    });

    it('tracks instalments until the booking is paid in full', function () {
        $this->actingAs($this->staff)->post(route('admin.bookings.payments.store', $this->booking), paymentForm(['amount' => '1,500,000']));

        expect($this->booking->refresh()->amountPaid())->toBe(1_500_000)
            ->and($this->booking->balance())->toBe(2_500_000)
            ->and($this->booking->balanceStatus())->toBe(BalanceStatus::Partial);

        $this->actingAs($this->staff)->post(route('admin.bookings.payments.store', $this->booking), paymentForm(['amount' => '2,500,000', 'method' => 'cash', 'reference' => '']));

        $this->booking->refresh();
        expect($this->booking->amountPaid())->toBe(4_000_000)
            ->and($this->booking->balance())->toBe(0)
            ->and($this->booking->balanceStatus())->toBe(BalanceStatus::Paid)
            ->and(Payment::query()->orderBy('id')->pluck('receipt_number')->all())->toBe(['RCT-2026-00001', 'RCT-2026-00002']);
    });

    it('starts receipt numbers again each year', function () {
        Payment::factory()->create(['receipt_number' => 'RCT-2025-00042', 'booking_id' => $this->booking->id, 'amount' => 100_000]);

        $this->actingAs($this->staff)->post(route('admin.bookings.payments.store', $this->booking), paymentForm());

        expect(Payment::query()->latest('id')->value('receipt_number'))->toBe('RCT-2026-00001');
    });

    it('refuses a payment above the balance', function () {
        Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 3_500_000]);

        $this->actingAs($this->staff)
            ->post(route('admin.bookings.payments.store', $this->booking), paymentForm(['amount' => '600,000']))
            ->assertSessionHasErrors(['amount' => 'That is more than the balance of UGX 500,000. Check the amount, or raise the quote first.']);

        expect(Payment::query()->count())->toBe(1);
    });

    it('refuses payments on bookings that are not approved', function (string $status) {
        $booking = Booking::factory()->create(['status' => $status, 'quoted_amount' => 1_000_000]);

        $this->actingAs($this->staff)
            ->post(route('admin.bookings.payments.store', $booking), paymentForm(['amount' => '100,000']))
            ->assertSessionHasErrors(['amount' => 'Payments can only be recorded on an approved booking with a quote.']);

        expect($booking->payments()->count())->toBe(0);
    })->with(['pending', 'rejected']);

    it('refuses payments before a quote is set', function () {
        $booking = Booking::factory()->approved()->create(['quoted_amount' => null]);

        $this->actingAs($this->staff)
            ->post(route('admin.bookings.payments.store', $booking), paymentForm())
            ->assertSessionHasErrors('amount');
    });

    it('validates the payment details', function (array $input, string $field, string $message) {
        $this->actingAs($this->staff)
            ->post(route('admin.bookings.payments.store', $this->booking), paymentForm($input))
            ->assertSessionHasErrors([$field => $message]);
    })->with([
        'zero' => [['amount' => '0'], 'amount', 'The amount must be more than zero.'],
        'decimals' => [['amount' => '1000.50'], 'amount', 'Enter the amount in whole Uganda shillings, e.g. 500,000.'],
        'mobile money without reference' => [['method' => 'airtel_money', 'reference' => ''], 'reference', 'Enter the transaction ID or bank reference for this payment method.'],
        'future date' => [['received_at' => '2026-11-19 09:00'], 'received_at', "The payment date can't be in the future."],
        'unknown method' => [['method' => 'bitcoin'], 'method', 'The selected method is invalid.'],
    ]);

    it('accepts cash without a reference', function () {
        $this->actingAs($this->staff)
            ->post(route('admin.bookings.payments.store', $this->booking), paymentForm(['method' => 'cash', 'reference' => '']))
            ->assertSessionHasNoErrors();
    });

    it('emails the customer a receipt with the new balance', function () {
        Notification::fake();

        $this->actingAs($this->staff)->post(route('admin.bookings.payments.store', $this->booking), paymentForm());

        Notification::assertSentOnDemand(PaymentRecorded::class, function (PaymentRecorded $notification, array $channels, $notifiable) {
            $mail = $notification->toMail($notifiable);
            $body = implode(' ', $mail->introLines);

            return $notifiable->routes['mail'] === 'amina@example.com'
                && str_contains($body, 'RCT-2026-00001')
                && str_contains($body, 'UGX 1,000,000')
                && str_contains($body, '**Balance remaining:** UGX 3,000,000');
        });

        expect(NotificationLog::query()->where('event', NotificationLog::EVENT_PAYMENT_RECORDED)->count())->toBe(1);
    });
});

describe('voiding payments', function () {
    it('lets the owner void a payment with a reason, which restores the balance', function () {
        $payment = Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 1_000_000]);
        expect($this->booking->balance())->toBe(3_000_000);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.void', $payment), ['void_reason' => 'Entered twice by mistake.'])
            ->assertRedirect(route('admin.bookings.show', $this->booking));

        $payment->refresh();
        expect($payment->status)->toBe(PaymentStatus::Voided)
            ->and($payment->voided_by)->toBe($this->admin->id)
            ->and($payment->voided_at)->not->toBeNull()
            ->and($payment->void_reason)->toBe('Entered twice by mistake.')
            ->and($this->booking->refresh()->balance())->toBe(4_000_000)
            ->and(Payment::query()->count())->toBe(1);
    });

    it('requires a reason', function () {
        $payment = Payment::factory()->create(['booking_id' => $this->booking->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.void', $payment), ['void_reason' => ''])
            ->assertSessionHasErrors(['void_reason' => 'Give a reason for voiding this payment.']);

        expect($payment->refresh()->status)->toBe(PaymentStatus::Confirmed);
    });

    it('returns 403 to staff', function () {
        $payment = Payment::factory()->create(['booking_id' => $this->booking->id]);

        $this->actingAs($this->staff)
            ->post(route('admin.payments.void', $payment), ['void_reason' => 'Wrong amount'])
            ->assertForbidden();

        expect($payment->refresh()->status)->toBe(PaymentStatus::Confirmed);
    });

    it('cannot void the same payment twice', function () {
        $payment = Payment::factory()->voided()->create(['booking_id' => $this->booking->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.void', $payment), ['void_reason' => 'Again'])
            ->assertForbidden();
    });

    it('never deletes a payment', function () {
        $payment = Payment::factory()->create(['booking_id' => $this->booking->id]);

        expect(fn () => $payment->delete())->toThrow(LogicException::class, 'Void the payment instead.');
    });
});

describe('quotes', function () {
    it('lets staff set or change the quote, typed with commas', function () {
        $booking = Booking::factory()->approved()->create(['quoted_amount' => null]);

        $this->actingAs($this->staff)
            ->patch(route('admin.bookings.quote.update', $booking), ['quoted_amount' => '5,250,000'])
            ->assertSessionHas('status', 'Quote set to UGX 5,250,000.');

        expect($booking->refresh()->quoted_amount)->toBe(5_250_000);
    });

    it('refuses a quote below what has been paid', function () {
        Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 2_000_000]);

        $this->actingAs($this->staff)
            ->patch(route('admin.bookings.quote.update', $this->booking), ['quoted_amount' => '1,500,000'])
            ->assertSessionHasErrors(['quoted_amount' => "The quote can't be less than the UGX 2,000,000 already paid. Void a payment first if it was recorded by mistake."]);

        expect($this->booking->refresh()->quoted_amount)->toBe(4_000_000);
    });

    it('ignores voided payments when checking a new quote', function () {
        Payment::factory()->voided()->create(['booking_id' => $this->booking->id, 'amount' => 3_000_000]);

        $this->actingAs($this->staff)
            ->patch(route('admin.bookings.quote.update', $this->booking), ['quoted_amount' => '1,000,000'])
            ->assertSessionHasNoErrors();
    });

    it('cannot be changed on a rejected booking', function () {
        $booking = Booking::factory()->rejected()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.quote.update', $booking), ['quoted_amount' => '1,000,000'])
            ->assertSessionHasErrors('quoted_amount');
    });
});

describe('receipts', function () {
    it('shows a printable receipt with the balance at that point', function () {
        $first = Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 1_000_000, 'receipt_number' => 'RCT-2026-00001', 'recorded_by' => $this->staff->id]);
        Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 2_000_000, 'receipt_number' => 'RCT-2026-00002']);

        $this->actingAs($this->staff)
            ->get(route('admin.payments.receipt', $first))
            ->assertOk()
            ->assertSee('RCT-2026-00001')
            ->assertSee($this->booking->reference)
            ->assertSee($this->booking->customer_name)
            ->assertSee('UGX 1,000,000')
            ->assertSee('UGX 3,000,000')
            ->assertSee('Received by Staff Joseph')
            ->assertSee('Print receipt');
    });

    it('marks a voided receipt as void', function () {
        $payment = Payment::factory()->voided()->create(['booking_id' => $this->booking->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.receipt', $payment))
            ->assertSee('VOID')
            ->assertSee('This receipt was voided');
    });

    it('returns 403 to customers', function () {
        $payment = Payment::factory()->create(['booking_id' => $this->booking->id]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.payments.receipt', $payment))
            ->assertForbidden();
    });
});

describe('booking page', function () {
    it('shows the quote, amount paid, balance and ledger', function () {
        Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 1_000_000, 'receipt_number' => 'RCT-2026-00001']);
        Payment::factory()->voided()->create(['booking_id' => $this->booking->id, 'amount' => 700_000, 'receipt_number' => 'RCT-2026-00002']);

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.show', $this->booking))
            ->assertSee('UGX 4,000,000')
            ->assertSee('UGX 1,000,000')
            ->assertSee('UGX 3,000,000')
            ->assertSee('Part paid')
            ->assertSee('RCT-2026-00001')
            ->assertSee('RCT-2026-00002')
            ->assertSee('Voided')
            ->assertSee('Record a payment');
    });

    it('notes money held on a cancelled booking', function () {
        Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 1_000_000]);

        $this->actingAs($this->admin)->patch(route('admin.bookings.status.update', $this->booking), ['status' => 'rejected', 'note' => 'Customer cancelled.']);

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.show', $this->booking))
            ->assertSee('This booking was cancelled with UGX 1,000,000 paid.')
            ->assertDontSee('Record a payment');

        expect(Payment::query()->sole()->status)->toBe(PaymentStatus::Confirmed);
    });
});
