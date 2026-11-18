<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\NotificationStatus;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\NotificationLog;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-18 10:00:00');
    $this->staff = User::factory()->staff()->create(['name' => 'Staff Joseph']);
    config()->set('lekobosi.admin.notify_email', 'owner@lekobosi.test');
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function phoneBookingForm(array $overrides = []): array
{
    return array_merge([
        'source' => 'phone',
        'fullname' => 'Mzee Okello',
        'email' => '',
        'phone' => '0772 555 444',
        'whatsapp' => '',
        'event_type' => 'introduction_ceremony',
        'date' => '2026-12-19',
        'location' => 'Butemba village',
        'guests' => '300',
        'details' => '',
    ], $overrides);
}

describe('entering a phone or walk-in booking', function () {
    it('creates a pending booking without an email, recording who entered it', function () {
        $response = $this->actingAs($this->staff)->post(route('admin.bookings.store'), phoneBookingForm());

        $booking = Booking::query()->sole();
        $response->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('status', "Booking {$booking->reference} created. Give the customer this reference so they can track it.");

        expect($booking->source)->toBe(BookingSource::Phone)
            ->and($booking->status)->toBe(BookingStatus::Pending)
            ->and($booking->customer_email)->toBeNull()
            ->and($booking->customer_phone)->toBe('+256772555444')
            ->and($booking->whatsapp_number)->toBe('+256772555444')
            ->and($booking->created_by)->toBe($this->staff->id)
            ->and($booking->user_id)->toBeNull()
            ->and(BookingStatusHistory::query()->sole()->changed_by)->toBe($this->staff->id);
    });

    it('alerts the owner and skips the customer email when there is no address', function () {
        $this->actingAs($this->staff)->post(route('admin.bookings.store'), phoneBookingForm());

        expect(NotificationLog::query()->where('recipient_type', 'admin')->sole()->status)->toBe(NotificationStatus::Sent)
            ->and(NotificationLog::query()->where('recipient_type', 'customer')->sole()->status)->toBe(NotificationStatus::Skipped);
    });

    it('records walk-in bookings too', function () {
        $this->actingAs($this->staff)->post(route('admin.bookings.store'), phoneBookingForm(['source' => 'walk_in']));

        expect(Booking::query()->sole()->source)->toBe(BookingSource::WalkIn);
    });

    it('only accepts phone or walk-in as the source', function (string $source) {
        $this->actingAs($this->staff)
            ->post(route('admin.bookings.store'), phoneBookingForm(['source' => $source]))
            ->assertSessionHasErrors('source');
    })->with(['online', '', 'email']);

    it('still refuses a full date', function () {
        Booking::factory()->approved()->on('2026-12-19')->create();

        $this->actingAs($this->staff)
            ->post(route('admin.bookings.store'), phoneBookingForm())
            ->assertSessionHasErrors(['date' => 'That date is already booked. Please choose another date.']);
    });

    it('shows who entered the booking', function () {
        $this->actingAs($this->staff)->post(route('admin.bookings.store'), phoneBookingForm());

        $this->actingAs($this->staff)
            ->get(route('admin.bookings.show', Booking::query()->sole()))
            ->assertSee('Phone')
            ->assertSee('entered by Staff Joseph');
    });

    it('returns 403 to customers', function () {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.bookings.create'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.bookings.store'), phoneBookingForm())->assertForbidden();
    });
});

describe('editing a booking', function () {
    it('lets staff correct a pending booking', function () {
        $booking = Booking::factory()->on('2026-12-12')->create(['customer_name' => 'Typo Nmae']);

        $this->actingAs($this->staff)
            ->patch(route('admin.bookings.update', $booking), phoneBookingForm(['fullname' => 'Correct Name', 'date' => '2026-12-13', 'guests' => '120']))
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHasNoErrors();

        $booking->refresh();
        expect($booking->customer_name)->toBe('Correct Name')
            ->and($booking->event_date->toDateString())->toBe('2026-12-13')
            ->and($booking->guest_count)->toBe(120)
            ->and($booking->source)->toBe(BookingSource::Online);
    });

    it('refuses to move a booking onto a full date', function () {
        Booking::factory()->approved()->on('2026-12-20')->create();
        $booking = Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->staff)
            ->patch(route('admin.bookings.update', $booking), phoneBookingForm(['date' => '2026-12-20']))
            ->assertSessionHasErrors('date');

        expect($booking->refresh()->event_date->toDateString())->toBe('2026-12-12');
    });

    it('allows other corrections when the booking stays on its (now full) date', function () {
        $booking = Booking::factory()->on('2026-12-20')->create();
        Booking::factory()->approved()->on('2026-12-20')->create();

        $this->actingAs($this->staff)
            ->patch(route('admin.bookings.update', $booking), phoneBookingForm(['date' => '2026-12-20', 'guests' => '50']))
            ->assertSessionHasNoErrors();
    });

    it('returns 403 once a booking has been decided', function (string $state) {
        $booking = Booking::factory()->{$state}()->create();

        $this->actingAs($this->staff)->get(route('admin.bookings.edit', $booking))->assertForbidden();
        $this->actingAs($this->staff)->patch(route('admin.bookings.update', $booking), phoneBookingForm())->assertForbidden();
    })->with(['approved', 'rejected']);
});

it('meets the spec acceptance flow: phone booking without email, payment, report', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($this->staff)->post(route('admin.bookings.store'), phoneBookingForm());
    $booking = Booking::query()->sole();

    $this->actingAs($admin)->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved', 'quoted_amount' => '3,000,000']);

    $this->actingAs($this->staff)
        ->post(route('admin.bookings.payments.store', $booking), ['amount' => '1,000,000', 'method' => 'cash', 'received_at' => '2026-11-18 09:00'])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->staff)
        ->get(route('admin.reports.payments', ['from' => '2026-11-01', 'to' => '2026-11-30']))
        ->assertOk()
        ->assertSee('Mzee Okello')
        ->assertSee('UGX 1,000,000');
});
