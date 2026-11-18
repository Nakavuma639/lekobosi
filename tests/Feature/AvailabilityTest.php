<?php

use App\Models\Booking;

it('lists only the fully booked dates in the month', function () {
    Booking::factory()->approved()->on('2026-12-05')->create(['customer_name' => 'Private Person']);
    Booking::factory()->approved()->on('2026-12-19')->create();
    Booking::factory()->on('2026-12-12')->create();
    Booking::factory()->rejected()->on('2026-12-13')->create();
    Booking::factory()->approved()->on('2027-01-02')->create();

    $this->getJson(route('availability', ['month' => '2026-12']))
        ->assertOk()
        ->assertExactJson([
            'month' => '2026-12',
            'full_dates' => ['2026-12-05', '2026-12-19'],
        ])
        ->assertDontSee('Private Person');
});

it('treats a date as open until approved bookings reach the daily capacity', function () {
    config()->set('lekobosi.bookings.max_events_per_day', 2);
    Booking::factory()->approved()->on('2026-12-05')->create();
    Booking::factory()->approved()->count(2)->on('2026-12-06')->create();

    $this->getJson(route('availability', ['month' => '2026-12']))
        ->assertJsonPath('full_dates', ['2026-12-06']);
});

it('rejects a malformed month with 422', function (string $month) {
    $this->getJson(route('availability', ['month' => $month]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('month');
})->with(['2026-13', 'december', '']);
