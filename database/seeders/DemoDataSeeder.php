<?php

namespace Database\Seeders;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Models\BookingConflictAttempt;
use App\Models\BookingStatusHistory;
use App\Models\ContactMessage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Fake data for trying the system out locally (spec §12: never real customers).
 * All demo accounts use the password "password". Refuses to run in production.
 */
class DemoDataSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'password';

    /**
     * Seed the database with a realistic month of fake activity.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DemoDataSeeder adds accounts with a known password and must not run in production.');
        }

        $owner = User::factory()->admin()->create(['name' => 'Demo Owner', 'email' => 'owner@demo.lekobosi.test']);
        $staff = User::factory()->staff()->create(['name' => 'Demo Staff', 'email' => 'staff@demo.lekobosi.test']);
        $customer = User::factory()->create(['name' => 'Demo Customer', 'email' => 'customer@demo.lekobosi.test']);
        User::factory()->count(5)->create();

        $today = today();

        // Requests still awaiting the owner's decision.
        foreach (range(1, 4) as $i) {
            $this->booking(BookingStatus::Pending, $today->copy()->addDays(10 + $i * 6));
        }
        $this->booking(BookingStatus::Pending, $today->copy()->addDays(30), ['user_id' => $customer->id, 'customer_name' => $customer->name, 'customer_email' => $customer->email, 'customer_phone' => $customer->phone]);
        $this->booking(BookingStatus::Pending, $today->copy()->addDays(21), ['source' => BookingSource::Phone, 'customer_email' => null, 'created_by' => $staff->id]);

        // Approved events with quotes and payments in every state.
        $paidInFull = $this->booking(BookingStatus::Approved, $today->copy()->addDays(5), ['quoted_amount' => 3_500_000], $owner);
        $this->payment($paidInFull, 1_500_000, PaymentMethod::MtnMomo, $staff, 'Deposit', daysAgo: 20);
        $this->payment($paidInFull, 2_000_000, PaymentMethod::Cash, $owner, 'Balance', daysAgo: 2);

        $partPaid = $this->booking(BookingStatus::Approved, $today->copy()->addDays(14), ['quoted_amount' => 6_800_000, 'event_type' => EventType::Wedding, 'guest_count' => 400, 'user_id' => $customer->id, 'customer_name' => $customer->name, 'customer_email' => $customer->email, 'customer_phone' => $customer->phone], $owner);
        $this->payment($partPaid, 2_000_000, PaymentMethod::AirtelMoney, $staff, 'Deposit', daysAgo: 6);
        $voided = $this->payment($partPaid, 2_000_000, PaymentMethod::AirtelMoney, $staff, 'Entered twice', daysAgo: 6);
        $voided->status = 'voided';
        $voided->voided_by = $owner->id;
        $voided->voided_at = now()->subDays(5);
        $voided->void_reason = 'Entered twice by mistake.';
        $voided->save();

        $this->booking(BookingStatus::Approved, $today->copy()->addDays(26), ['quoted_amount' => 2_400_000, 'event_type' => EventType::CorporateEvent], $owner);
        $this->booking(BookingStatus::Approved, $today->copy()->addDays(40), ['quoted_amount' => null], $owner);

        // Declined and cancelled requests.
        $this->booking(BookingStatus::Rejected, $today->copy()->addDays(5), ['decision_note' => 'We are already catering an event that day.'], $owner);
        $cancelled = $this->booking(BookingStatus::Approved, $today->copy()->addDays(33), ['quoted_amount' => 1_800_000], $owner);
        $this->payment($cancelled, 500_000, PaymentMethod::Cash, $staff, 'Deposit', daysAgo: 10);
        $cancelled->status = BookingStatus::Rejected;
        $cancelled->decision_note = 'Customer postponed the event indefinitely.';
        $cancelled->save();
        $this->history($cancelled, BookingStatus::Approved, BookingStatus::Rejected, $owner, $cancelled->decision_note);

        BookingConflictAttempt::factory()->count(2)->create(['requested_date' => $paidInFull->event_date->toDateString()]);
        ContactMessage::factory()->count(3)->create();
        ContactMessage::factory()->read()->count(2)->create(['read_by' => $owner->id]);
    }

    /**
     * Create a booking with a matching status history.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function booking(BookingStatus $status, mixed $date, array $attributes = [], ?User $decidedBy = null): Booking
    {
        $booking = Booking::factory()->on($date->toDateString())->create(array_merge([
            'status' => $status,
            'decided_by' => $status === BookingStatus::Pending ? null : $decidedBy?->id,
        ], $attributes));

        $this->history($booking, null, BookingStatus::Pending, $booking->created_by ? User::find($booking->created_by) : null);

        if ($status !== BookingStatus::Pending) {
            $this->history($booking, BookingStatus::Pending, $status === BookingStatus::Rejected ? BookingStatus::Rejected : BookingStatus::Approved, $decidedBy, $booking->decision_note);
        }

        return $booking;
    }

    private function history(Booking $booking, ?BookingStatus $from, BookingStatus $to, ?User $by, ?string $note = null): void
    {
        $history = new BookingStatusHistory;
        $history->booking_id = $booking->id;
        $history->from_status = $from;
        $history->to_status = $to;
        $history->changed_by = $by?->id;
        $history->note = $note;
        $history->save();
    }

    private static int $receipt = 0;

    private function payment(Booking $booking, int $amount, PaymentMethod $method, User $recordedBy, string $note, int $daysAgo): Payment
    {
        self::$receipt++;

        return Payment::factory()->create([
            'booking_id' => $booking->id,
            'receipt_number' => 'RCT-'.now()->year.'-'.str_pad((string) self::$receipt, 5, '0', STR_PAD_LEFT),
            'amount' => $amount,
            'method' => $method,
            'reference' => $method === PaymentMethod::Cash ? null : fake()->bothify('MP######.####.?#####'),
            'received_at' => now()->subDays($daysAgo),
            'recorded_by' => $recordedBy->id,
            'note' => $note,
        ]);
    }
}
