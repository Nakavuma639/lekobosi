<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-18 10:00:00');
    $this->admin = User::factory()->admin()->create();
});

describe('approving', function () {
    it('approves a pending booking and records who decided and when', function () {
        $booking = Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved', 'quoted_amount' => '4,500,000'])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('status', "Booking {$booking->reference} is now Approved.");

        $booking->refresh();
        expect($booking->status)->toBe(BookingStatus::Approved)
            ->and($booking->decided_by)->toBe($this->admin->id)
            ->and($booking->status_changed_at->toDateTimeString())->toBe('2026-11-18 10:00:00')
            ->and($booking->quoted_amount)->toBe(4_500_000);

        $history = BookingStatusHistory::query()->where('booking_id', $booking->id)->sole();
        expect($history->from_status)->toBe(BookingStatus::Pending)
            ->and($history->to_status)->toBe(BookingStatus::Approved)
            ->and($history->changed_by)->toBe($this->admin->id);
    });

    it('shows the new status to the customer on the track page', function () {
        $booking = Booking::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved']);

        $this->get(route('bookings.track', ['reference' => $booking->reference]))
            ->assertSee('Approved')
            ->assertSee('Your date is confirmed.');
    });

    it('refuses to approve a second booking for a full date', function () {
        Booking::factory()->approved()->on('2026-12-12')->create();
        $second = Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->admin)
            ->from(route('admin.bookings.show', $second))
            ->patch(route('admin.bookings.status.update', $second), ['status' => 'approved'])
            ->assertRedirect(route('admin.bookings.show', $second))
            ->assertSessionHasErrors(['status' => '12 December 2026 is already fully booked. Reject this request or cancel the other approved booking first.']);

        expect($second->refresh()->status)->toBe(BookingStatus::Pending)
            ->and(BookingStatusHistory::query()->where('booking_id', $second->id)->count())->toBe(0);
    });

    it('returns 409 Conflict to JSON clients for a full date', function () {
        Booking::factory()->approved()->on('2026-12-12')->create();
        $second = Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.bookings.status.update', $second), ['status' => 'approved'])
            ->assertStatus(409)
            ->assertJsonPath('message', '12 December 2026 is already fully booked. Reject this request or cancel the other approved booking first.');
    });

    it('approves up to the configured daily capacity', function () {
        config()->set('lekobosi.bookings.max_events_per_day', 2);
        Booking::factory()->approved()->on('2026-12-12')->create();
        $second = Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $second), ['status' => 'approved'])
            ->assertSessionHasNoErrors();

        expect($second->refresh()->status)->toBe(BookingStatus::Approved);
    });

    it('frees the date again when an approved booking is cancelled', function () {
        $first = Booking::factory()->approved()->on('2026-12-12')->create();
        $second = Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->admin)->patch(route('admin.bookings.status.update', $first), ['status' => 'rejected', 'note' => 'Customer cancelled.']);
        $this->actingAs($this->admin)->patch(route('admin.bookings.status.update', $second), ['status' => 'approved'])->assertSessionHasNoErrors();

        expect($second->refresh()->status)->toBe(BookingStatus::Approved);
    });
});

describe('rejecting', function () {
    it('requires a reason', function () {
        $booking = Booking::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'rejected', 'note' => ''])
            ->assertSessionHasErrors(['note' => 'Give the customer a reason for rejecting the booking.']);

        expect($booking->refresh()->status)->toBe(BookingStatus::Pending);
    });

    it('stores the reason and shows it to the customer', function () {
        $booking = Booking::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'rejected', 'note' => 'We are away that weekend.']);

        $booking->refresh();
        expect($booking->status)->toBe(BookingStatus::Rejected)
            ->and($booking->decision_note)->toBe('We are away that weekend.');

        $this->get(route('bookings.track', ['reference' => $booking->reference]))
            ->assertSee('We are away that weekend.');
    });

    it('lets the owner cancel an approved booking with a reason', function () {
        $booking = Booking::factory()->approved()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'rejected', 'note' => 'Venue flooded.'])
            ->assertSessionHasNoErrors();

        $history = BookingStatusHistory::query()->where('booking_id', $booking->id)->sole();
        expect($history->from_status)->toBe(BookingStatus::Approved)
            ->and($history->to_status)->toBe(BookingStatus::Rejected)
            ->and($history->note)->toBe('Venue flooded.');
    });
});

describe('rules', function () {
    it('does not reopen a rejected booking', function () {
        $booking = Booking::factory()->rejected()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved'])
            ->assertSessionHasErrors(['status' => "A booking that is Rejected can't be changed to Approved."]);

        expect($booking->refresh()->status)->toBe(BookingStatus::Rejected);
    });

    it('does not approve an already approved booking twice', function () {
        $booking = Booking::factory()->approved()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved'])
            ->assertSessionHasErrors('status');
    });

    it('only accepts approved or rejected as the new status', function () {
        $booking = Booking::factory()->approved()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'pending'])
            ->assertSessionHasErrors('status');
    });

    it('rejects a quote that is not a whole number of shillings', function () {
        $booking = Booking::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved', 'quoted_amount' => '4500.50'])
            ->assertSessionHasErrors(['quoted_amount' => 'Enter the quote in whole Uganda shillings, without commas.']);
    });
});

describe('who can decide', function () {
    it('returns 403 to staff', function () {
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->staff()->create())
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved'])
            ->assertForbidden();

        expect($booking->refresh()->status)->toBe(BookingStatus::Pending);
    });

    it('returns 403 to customers, even for their own booking', function () {
        $customer = User::factory()->create();
        $booking = Booking::factory()->forCustomer($customer)->create();

        $this->actingAs($customer)
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved'])
            ->assertForbidden();
    });

    it('denies the decide ability to staff at the policy level too', function () {
        $booking = Booking::factory()->create();

        expect(User::factory()->staff()->create()->can('decide', $booking))->toBeFalse()
            ->and($this->admin->can('decide', $booking))->toBeTrue();
    });
});
