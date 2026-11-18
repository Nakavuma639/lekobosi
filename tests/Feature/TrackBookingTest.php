<?php

use App\Http\Controllers\TrackBookingController;
use App\Models\Booking;

it('shows the empty track page without a reference', function () {
    $this->get(route('bookings.track'))
        ->assertOk()
        ->assertSee('Track your booking')
        ->assertDontSee(TrackBookingController::NOT_FOUND_MESSAGE);
});

it('shows the status, event and date for a known reference', function () {
    $booking = Booking::factory()->on('2026-12-12')->create(['event_type' => 'wedding']);

    $this->get(route('bookings.track', ['reference' => $booking->reference]))
        ->assertOk()
        ->assertSee($booking->reference)
        ->assertSee('Pending')
        ->assertSee('Wedding')
        ->assertSee('Saturday, 12 December 2026');
});

it('finds a booking however the reference is typed', function (callable $type) {
    $booking = Booking::factory()->create(['reference' => 'LKB-7K3QX9MD']);

    $this->get(route('bookings.track', ['reference' => $type($booking->reference)]))
        ->assertOk()
        ->assertSee('LKB-7K3QX9MD');
})->with([
    'lowercase' => fn () => fn (string $reference) => strtolower($reference),
    'surrounding spaces' => fn () => fn (string $reference) => "  {$reference}  ",
    'code only' => fn () => fn (string $reference) => substr($reference, 4),
]);

it('shows the latest status after the owner decides', function () {
    $booking = Booking::factory()->approved()->create();

    $this->get(route('bookings.track', ['reference' => $booking->reference]))
        ->assertSee('Approved')
        ->assertSee('Your date is confirmed.');
});

it('shows the rejection reason only for rejected bookings', function () {
    $rejected = Booking::factory()->rejected()->create(['decision_note' => 'Fully booked that weekend.']);
    $pendingWithNote = Booking::factory()->create(['decision_note' => 'Internal: check with chef.']);

    $this->get(route('bookings.track', ['reference' => $rejected->reference]))
        ->assertSee('Fully booked that weekend.');

    $this->get(route('bookings.track', ['reference' => $pendingWithNote->reference]))
        ->assertDontSee('Internal: check with chef.');
});

it('never shows private booking details', function () {
    $booking = Booking::factory()->create([
        'customer_name' => 'Private Name',
        'customer_email' => 'private@example.com',
        'customer_phone' => '+256772555444',
        'whatsapp_number' => '+256752555444',
        'event_location' => 'Secret Venue Road',
        'extra_requirements' => 'Private requirements',
        'quoted_amount' => 4_500_000,
    ]);

    $this->get(route('bookings.track', ['reference' => $booking->reference]))
        ->assertOk()
        ->assertDontSee('Private Name')
        ->assertDontSee('private@example.com')
        ->assertDontSee('+256772555444')
        ->assertDontSee('+256752555444')
        ->assertDontSee('Secret Venue Road')
        ->assertDontSee('Private requirements')
        ->assertDontSee('4,500,000')
        ->assertDontSee('4500000');
});

it('returns 404 with the same message for any unknown reference', function (string $reference) {
    Booking::factory()->create(['reference' => 'LKB-7K3QX9MD']);

    $this->get(route('bookings.track', ['reference' => $reference]))
        ->assertNotFound()
        ->assertSee(TrackBookingController::NOT_FOUND_MESSAGE)
        ->assertDontSee('data-testid="tracking-result"', escape: false);
})->with([
    'well-formed but unknown' => 'LKB-22222222',
    'one character off' => 'LKB-7K3QX9ME',
    'nonsense' => 'hello',
    'very long' => str_repeat('A', 300),
    'sql-like' => "' OR 1=1 --",
]);

it('throttles reference lookups to ten a minute', function () {
    foreach (range(1, 10) as $guess) {
        $this->get(route('bookings.track', ['reference' => "LKB-GUESS{$guess}"]))->assertNotFound();
    }

    $this->get(route('bookings.track', ['reference' => 'LKB-GUESS11']))->assertTooManyRequests();
});

it('does not count visits to the empty page against the lookup limit', function () {
    foreach (range(1, 15) as $visit) {
        $this->get(route('bookings.track'))->assertOk();
    }

    $booking = Booking::factory()->create();

    $this->get(route('bookings.track', ['reference' => $booking->reference]))->assertOk();
});

it('links from the booking confirmation straight to the tracking result', function () {
    $this->travelTo('2026-11-18 10:00:00');

    $this->post(route('bookings.store'), [
        'fullname' => 'Amina Nakato',
        'email' => 'amina@example.com',
        'phone' => '0772123456',
        'event_type' => 'wedding',
        'date' => '2026-12-12',
        'location' => 'Kyankwanzi',
        'guests' => '100',
    ]);

    $reference = Booking::query()->sole()->reference;

    $this->get(route('bookings.confirmation'))
        ->assertSee(route('bookings.track', ['reference' => $reference]), escape: false);

    $this->get(route('bookings.track', ['reference' => $reference]))
        ->assertOk()
        ->assertSee('data-testid="tracking-result"', escape: false);
});
