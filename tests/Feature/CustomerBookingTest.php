<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->booking = Booking::factory()->approved()->forCustomer($this->customer)->create(['quoted_amount' => 3_000_000]);
});

it('shows the customer their quote, payments and balance', function () {
    Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 1_000_000, 'receipt_number' => 'RCT-2026-00001']);
    Payment::factory()->voided()->create(['booking_id' => $this->booking->id, 'amount' => 500_000, 'receipt_number' => 'RCT-2026-00002']);

    $this->actingAs($this->customer)
        ->get(route('customer.bookings.show', $this->booking))
        ->assertOk()
        ->assertSee('UGX 3,000,000')
        ->assertSee('UGX 1,000,000')
        ->assertSee('UGX 2,000,000')
        ->assertSee('Part paid')
        ->assertSee('RCT-2026-00001')
        ->assertDontSee('RCT-2026-00002');
});

it('shows each booking\'s balance on the account page', function () {
    Payment::factory()->create(['booking_id' => $this->booking->id, 'amount' => 1_000_000]);

    $this->actingAs($this->customer)
        ->get(route('customer.dashboard'))
        ->assertSee('Paid UGX 1,000,000 of UGX 3,000,000')
        ->assertSee('Balance UGX 2,000,000')
        ->assertSee(route('customer.bookings.show', $this->booking));
});

it('returns 404 for another customer\'s booking', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('customer.bookings.show', $this->booking))
        ->assertNotFound();
});

it('sends guests to the login page', function () {
    $this->get(route('customer.bookings.show', $this->booking))->assertRedirect(route('login'));
});
