<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-18 10:00:00');
    $this->admin = User::factory()->admin()->create();

    $this->unpaid = Booking::factory()->approved()->on('2026-12-05')->create(['customer_name' => 'Unpaid Customer', 'quoted_amount' => 2_000_000]);
    $this->partial = Booking::factory()->approved()->on('2026-12-12')->create(['customer_name' => 'Partial Customer', 'quoted_amount' => 3_000_000]);
    $this->paid = Booking::factory()->approved()->on('2027-01-09')->create(['customer_name' => 'Paid Customer', 'quoted_amount' => 1_000_000]);
    $this->cancelled = Booking::factory()->rejected()->create(['customer_name' => 'Cancelled Customer', 'quoted_amount' => 5_000_000]);
    Booking::factory()->approved()->create(['customer_name' => 'Unquoted Customer', 'quoted_amount' => null]);

    Payment::factory()->create(['booking_id' => $this->partial->id, 'amount' => 1_200_000]);
    Payment::factory()->voided()->create(['booking_id' => $this->partial->id, 'amount' => 900_000]);
    Payment::factory()->create(['booking_id' => $this->paid->id, 'amount' => 1_000_000]);
    Payment::factory()->create(['booking_id' => $this->cancelled->id, 'amount' => 500_000]);
});

it('shows live money totals', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.payments.index'))
        ->assertOk()
        ->assertViewHas('totals', [
            'quoted' => 6_000_000,
            'collected' => 2_200_000,
            'outstanding' => 3_800_000,
            'held_on_cancelled' => 500_000,
        ])
        ->assertViewHas('unquotedCount', 1);
});

it('lists approved bookings with quote, paid and balance', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.payments.index'))
        ->assertSee('Partial Customer')
        ->assertSee('UGX 1,800,000')
        ->assertSee('Unquoted Customer')
        ->assertDontSee('Cancelled Customer');
});

it('filters by payment status', function (string $balance, string $shown, array $hidden) {
    $response = $this->actingAs($this->admin)->get(route('admin.payments.index', ['balance' => $balance]));

    $response->assertSee($shown);
    foreach ($hidden as $name) {
        $response->assertDontSee($name);
    }
})->with([
    'unpaid' => ['unpaid', 'Unpaid Customer', ['Partial Customer', 'Paid Customer', 'Unquoted Customer']],
    'partial' => ['partial', 'Partial Customer', ['Unpaid Customer', 'Paid Customer', 'Unquoted Customer']],
    'paid' => ['paid', 'Paid Customer', ['Unpaid Customer', 'Partial Customer', 'Unquoted Customer']],
]);

it('can show outstanding balances only', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.payments.index', ['outstanding' => 1]))
        ->assertSee('Unpaid Customer')
        ->assertSee('Partial Customer')
        ->assertDontSee('Paid Customer')
        ->assertDontSee('Unquoted Customer');
});

it('filters by event date range', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.payments.index', ['from' => '2026-12-01', 'to' => '2026-12-31']))
        ->assertSee('Unpaid Customer')
        ->assertSee('Partial Customer')
        ->assertDontSee('Paid Customer');
});

it('shows the outstanding total on the dashboard', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertViewHas('outstandingBalance', 3_800_000)
        ->assertSee('UGX 3,800,000');
});

it('is open to staff but not customers', function () {
    $this->actingAs(User::factory()->staff()->create())->get(route('admin.payments.index'))->assertOk();
    $this->actingAs(User::factory()->create())->get(route('admin.payments.index'))->assertForbidden();
});
