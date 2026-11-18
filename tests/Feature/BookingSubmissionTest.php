<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Models\Booking;
use App\Models\BookingConflictAttempt;
use App\Models\BookingStatusHistory;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-18 10:00:00'));
});

/**
 * A valid booking form submission using the client's field names.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function bookingForm(array $overrides = []): array
{
    return array_merge([
        'fullname' => 'Amina Nakato',
        'email' => 'amina@example.com',
        'phone' => '0772 123 456',
        'whatsapp' => '',
        'event_type' => 'wedding',
        'date' => '2026-12-12',
        'location' => 'Kyankwanzi Parish Hall',
        'guests' => '250',
        'details' => 'Buffet for the reception.',
    ], $overrides);
}

describe('a valid request', function () {
    it('stores a pending booking and shows the reference on the confirmation page', function () {
        $response = $this->post(route('bookings.store'), bookingForm());

        $booking = Booking::query()->sole();

        $response->assertRedirect(route('bookings.confirmation'));
        $this->followRedirects($response)
            ->assertOk()
            ->assertSee($booking->reference)
            ->assertSee('Wedding')
            ->assertSee('Saturday, 12 December 2026');

        expect($booking->reference)->toMatch('/^LKB-[2-9A-HJKMNP-Z]{8}$/')
            ->and($booking->status)->toBe(BookingStatus::Pending)
            ->and($booking->source)->toBe(BookingSource::Online)
            ->and($booking->customer_name)->toBe('Amina Nakato')
            ->and($booking->customer_email)->toBe('amina@example.com')
            ->and($booking->customer_phone)->toBe('+256772123456')
            ->and($booking->event_type)->toBe(EventType::Wedding)
            ->and($booking->event_date->toDateString())->toBe('2026-12-12')
            ->and($booking->event_location)->toBe('Kyankwanzi Parish Hall')
            ->and($booking->guest_count)->toBe(250)
            ->and($booking->extra_requirements)->toBe('Buffet for the reception.')
            ->and($booking->user_id)->toBeNull();
    });

    it('writes the first status history row', function () {
        $this->post(route('bookings.store'), bookingForm());

        $history = BookingStatusHistory::query()->sole();

        expect($history->booking_id)->toBe(Booking::query()->sole()->id)
            ->and($history->from_status)->toBeNull()
            ->and($history->to_status)->toBe(BookingStatus::Pending)
            ->and($history->changed_by)->toBeNull();
    });

    it('uses the phone number for WhatsApp when none is given', function () {
        $this->post(route('bookings.store'), bookingForm(['whatsapp' => '']));

        expect(Booking::query()->sole()->whatsapp_number)->toBe('+256772123456');
    });

    it('stores a separate WhatsApp number in E.164', function () {
        $this->post(route('bookings.store'), bookingForm(['whatsapp' => '256 752 000 111']));

        expect(Booking::query()->sole()->whatsapp_number)->toBe('+256752000111');
    });

    it('strips HTML from free-text fields', function () {
        $this->post(route('bookings.store'), bookingForm([
            'fullname' => '<b>Amina</b> Nakato',
            'details' => '<script>alert(1)</script>Vegetarian options',
        ]));

        $booking = Booking::query()->sole();

        expect($booking->customer_name)->toBe('Amina Nakato')
            ->and($booking->extra_requirements)->toBe('alert(1)Vegetarian options');
    });

    it('ignores attempts to set protected fields', function () {
        $this->post(route('bookings.store'), bookingForm([
            'status' => 'approved',
            'reference' => 'LKB-HACKED00',
            'decided_by' => 1,
            'quoted_amount' => 1,
            'user_id' => 99,
        ]));

        $booking = Booking::query()->sole();

        expect($booking->status)->toBe(BookingStatus::Pending)
            ->and($booking->reference)->not->toBe('LKB-HACKED00')
            ->and($booking->decided_by)->toBeNull()
            ->and($booking->quoted_amount)->toBeNull()
            ->and($booking->user_id)->toBeNull();
    });
});

describe('signed-in users', function () {
    it('links the booking to the signed-in customer', function () {
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('bookings.store'), bookingForm());

        expect(Booking::query()->sole()->user_id)->toBe($customer->id);
    });

    it('does not link a booking made by a staff member to their account', function () {
        $this->actingAs(User::factory()->staff()->create())->post(route('bookings.store'), bookingForm());

        expect(Booking::query()->sole()->user_id)->toBeNull();
    });

    it('prefills the form with the customer details', function () {
        $customer = User::factory()->create(['name' => 'Joseph Kato', 'phone' => '+256772999888']);

        $this->actingAs($customer)
            ->get(route('bookings.create'))
            ->assertSee('value="Joseph Kato"', escape: false)
            ->assertSee('value="+256772999888"', escape: false);
    });
});

describe('validation', function () {
    it('reports every missing required field', function () {
        $this->post(route('bookings.store'), [])
            ->assertSessionHasErrors(['fullname', 'email', 'phone', 'event_type', 'date', 'location', 'guests']);

        expect(Booking::query()->count())->toBe(0);
    });

    it('rejects an invalid field with a clear message', function (array $input, string $field, string $message) {
        $this->post(route('bookings.store'), bookingForm($input))
            ->assertSessionHasErrors([$field => $message]);

        expect(Booking::query()->count())->toBe(0);
    })->with([
        'today is too soon' => [['date' => '2026-11-18'], 'date', 'Choose a date from tomorrow onwards.'],
        'past date' => [['date' => '2026-01-01'], 'date', 'Choose a date from tomorrow onwards.'],
        'more than two years ahead' => [['date' => '2028-11-19'], 'date', 'We take bookings up to two years ahead. Choose an earlier date.'],
        'unknown event type' => [['event_type' => 'funeral'], 'event_type', 'Choose one of the event types in the list.'],
        'zero guests' => [['guests' => '0'], 'guests', 'The number of guests field must be at least 1.'],
        'too many guests' => [['guests' => '5001'], 'guests', 'For more than 5000 guests, please call us to plan the event together.'],
        'non-Ugandan phone' => [['phone' => '12345'], 'phone', 'Enter a valid Ugandan phone number, e.g. 0772 123456.'],
        'bad WhatsApp number' => [['whatsapp' => 'abc'], 'whatsapp', 'Enter a valid Ugandan phone number, e.g. 0772 123456.'],
        'bad email' => [['email' => 'not-an-email'], 'email', 'The email field must be a valid email address.'],
        'details too long' => [['details' => str_repeat('a', 2001)], 'details', 'The additional requirements field must not be greater than 2000 characters.'],
    ]);

    it('accepts tomorrow, the earliest allowed date', function () {
        $this->post(route('bookings.store'), bookingForm(['date' => '2026-11-19']))
            ->assertSessionHasNoErrors();
    });

    it('turns away submissions that fill in the honeypot field', function () {
        $this->post(route('bookings.store'), bookingForm(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        expect(Booking::query()->count())->toBe(0);
    });
});

describe('date availability', function () {
    it('rejects a full date, logs the attempt and creates no booking', function () {
        Booking::factory()->approved()->on('2026-12-12')->create();

        $this->post(route('bookings.store'), bookingForm())
            ->assertSessionHasErrors(['date' => 'That date is already booked. Please choose another date.']);

        expect(Booking::query()->count())->toBe(1);

        $attempt = BookingConflictAttempt::query()->sole();
        expect($attempt->customer_name)->toBe('Amina Nakato')
            ->and($attempt->customer_phone)->toBe('+256772123456')
            ->and($attempt->customer_email)->toBe('amina@example.com')
            ->and($attempt->event_type)->toBe(EventType::Wedding)
            ->and($attempt->requested_date->toDateString())->toBe('2026-12-12');
    });

    it('accepts a date that only has pending or rejected bookings', function () {
        Booking::factory()->on('2026-12-12')->create();
        Booking::factory()->rejected()->on('2026-12-12')->create();

        $this->post(route('bookings.store'), bookingForm())->assertSessionHasNoErrors();

        expect(Booking::query()->count())->toBe(3)
            ->and(BookingConflictAttempt::query()->count())->toBe(0);
    });

    it('allows more events per day when the capacity is raised', function () {
        config()->set('lekobosi.bookings.max_events_per_day', 2);
        Booking::factory()->approved()->on('2026-12-12')->create();

        $this->post(route('bookings.store'), bookingForm())->assertSessionHasNoErrors();
    });
});

describe('confirmation page', function () {
    it('redirects to the booking form when visited directly', function () {
        Booking::factory()->create();

        $this->get(route('bookings.confirmation'))->assertRedirect(route('bookings.create'));
    });
});

it('throttles repeated submissions from the same visitor', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('bookings.store'), bookingForm(['date' => '2026-12-'.(10 + $attempt)]));
    }

    $this->post(route('bookings.store'), bookingForm(['date' => '2026-12-20']))->assertTooManyRequests();
});
