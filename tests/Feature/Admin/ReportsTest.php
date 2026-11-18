<?php

use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Models\BookingConflictAttempt;
use App\Models\Payment;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-18 10:00:00');
    $this->admin = User::factory()->admin()->create();
    $this->staff = User::factory()->staff()->create();
});

describe('bookings report', function () {
    it('counts bookings in the range by status, type and source', function () {
        Booking::factory()->approved()->on('2026-12-05')->create(['event_type' => 'wedding', 'guest_count' => 200]);
        Booking::factory()->on('2026-12-12')->create(['event_type' => 'wedding', 'source' => 'phone']);
        Booking::factory()->rejected()->on('2026-12-20')->create(['event_type' => 'conference']);
        Booking::factory()->approved()->on('2027-01-10')->create();

        $this->actingAs($this->staff)
            ->get(route('admin.reports.bookings', ['from' => '2026-12-01', 'to' => '2026-12-31']))
            ->assertOk()
            ->assertViewHas('report', function (array $report) {
                return $report['bookings']->count() === 3
                    && $report['byStatus'] === ['Pending' => 1, 'Approved' => 1, 'Rejected' => 1]
                    && $report['byEventType']['Wedding'] === 2
                    && $report['bySource']['Phone'] === 1
                    && $report['guests'] === 200;
            });
    });

    it('defaults to the current month', function () {
        $this->actingAs($this->staff)
            ->get(route('admin.reports.bookings'))
            ->assertSee('1 Nov 2026 to 30 Nov 2026');
    });
});

describe('payments report', function () {
    it('totals confirmed payments by method and keeps voided ones separate', function () {
        Payment::factory()->create(['amount' => 1_000_000, 'method' => PaymentMethod::Cash, 'received_at' => '2026-11-03 10:00']);
        Payment::factory()->create(['amount' => 500_000, 'method' => PaymentMethod::MtnMomo, 'received_at' => '2026-11-10 10:00']);
        Payment::factory()->voided()->create(['amount' => 300_000, 'method' => PaymentMethod::Cash, 'received_at' => '2026-11-11 10:00']);
        Payment::factory()->create(['amount' => 9_000_000, 'received_at' => '2026-10-31 23:00']);

        $this->actingAs($this->staff)
            ->get(route('admin.reports.payments', ['from' => '2026-11-01', 'to' => '2026-11-30']))
            ->assertViewHas('report', function (array $report) {
                return $report['confirmedTotal'] === 1_500_000
                    && $report['voidedTotal'] === 300_000
                    && $report['byMethod']['Cash'] === 1_000_000
                    && $report['byMethod']['MTN Mobile Money'] === 500_000
                    && $report['payments']->count() === 3;
            })
            ->assertSee('UGX 1,500,000');
    });
});

describe('CSV export', function () {
    it('lets the owner download the bookings report', function () {
        Booking::factory()->approved()->on('2026-11-25')->create(['customer_name' => 'Grace Achieng', 'quoted_amount' => 2_000_000]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.bookings', ['from' => '2026-11-01', 'to' => '2026-11-30', 'format' => 'csv']))
            ->assertOk()
            ->assertDownload('lekobosi-bookings-2026-11-01-to-2026-11-30.csv');

        $csv = $response->streamedContent();
        expect($csv)->toStartWith("\xEF\xBB\xBF")
            ->toContain('Reference,"Event date","Event type",Customer')
            ->toContain('Grace Achieng')
            ->toContain('2000000');
    });

    it('lets the owner download the payments report', function () {
        Payment::factory()->create(['amount' => 750_000, 'receipt_number' => 'RCT-2026-00007', 'received_at' => '2026-11-05 10:00']);

        $csv = $this->actingAs($this->admin)
            ->get(route('admin.reports.payments', ['from' => '2026-11-01', 'to' => '2026-11-30', 'format' => 'csv']))
            ->assertOk()
            ->streamedContent();

        expect($csv)->toContain('RCT-2026-00007')->toContain('750000');
    });

    it('stops spreadsheet formulas typed by customers from running', function () {
        Booking::factory()->on('2026-11-25')->create(['customer_name' => '=HYPERLINK("http://evil.example","Click")']);

        $csv = $this->actingAs($this->admin)
            ->get(route('admin.reports.bookings', ['from' => '2026-11-01', 'to' => '2026-11-30', 'format' => 'csv']))
            ->streamedContent();

        expect($csv)->toContain("'=HYPERLINK")
            ->not->toContain(',=HYPERLINK');
    });

    it('returns 403 to staff', function () {
        $this->actingAs($this->staff)
            ->get(route('admin.reports.bookings', ['format' => 'csv']))
            ->assertForbidden();

        $this->actingAs($this->staff)
            ->get(route('admin.reports.bookings'))
            ->assertDontSee('Download CSV');
    });
});

it('keeps reports away from customers', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.reports.bookings'))->assertForbidden();
    $this->actingAs(User::factory()->create())->get(route('admin.reports.payments'))->assertForbidden();
});

describe('turned-away requests', function () {
    it('lists requests refused for a full date, newest first', function () {
        BookingConflictAttempt::factory()->create(['customer_name' => 'Older Caller', 'created_at' => now()->subDay()]);
        BookingConflictAttempt::factory()->create(['customer_name' => 'Newer Caller', 'customer_phone' => '+256772000999']);

        $this->actingAs($this->admin)
            ->get(route('admin.conflict-attempts'))
            ->assertOk()
            ->assertSeeInOrder(['Newer Caller', 'Older Caller'])
            ->assertSee('https://wa.me/256772000999');
    });

    it('is for the owner only', function () {
        $this->actingAs($this->staff)->get(route('admin.conflict-attempts'))->assertForbidden();
    });
});
