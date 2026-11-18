<?php

use App\Enums\BookingSource;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-18 10:00:00');
    $this->admin = User::factory()->admin()->create(['name' => 'Owner Harriet']);
});

describe('access', function () {
    it('returns 403 to customers on every back-office page', function (Closure $url) {
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get($url($booking))
            ->assertForbidden();
    })->with([
        'dashboard' => fn (Booking $booking) => route('admin.dashboard'),
        'booking list' => fn (Booking $booking) => route('admin.bookings.index'),
        'booking detail' => fn (Booking $booking) => route('admin.bookings.show', $booking),
        'calendar' => fn (Booking $booking) => route('admin.calendar'),
    ]);

    it('sends guests to the login page', function () {
        $this->get(route('admin.bookings.index'))->assertRedirect(route('login'));
    });

    it('lets staff view the list, details and calendar', function () {
        $booking = Booking::factory()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('admin.bookings.index'))->assertOk()->assertSee($booking->reference);
        $this->actingAs($staff)->get(route('admin.bookings.show', $booking))->assertOk()->assertSee('Only the owner can approve or reject bookings.');
        $this->actingAs($staff)->get(route('admin.calendar'))->assertOk();
    });

    it('uses the booking reference in admin URLs, not the numeric id', function () {
        $booking = Booking::factory()->create();

        expect(route('admin.bookings.show', $booking))->toEndWith("/admin/bookings/{$booking->reference}");
        $this->actingAs($this->admin)->get("/admin/bookings/{$booking->id}")->assertNotFound();
    });
});

describe('booking list', function () {
    it('filters by status', function () {
        $pending = Booking::factory()->create();
        $approved = Booking::factory()->approved()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['status' => 'approved']))
            ->assertSee($approved->reference)
            ->assertDontSee($pending->reference);
    });

    it('searches by name, reference or phone in any format', function (string $term) {
        $match = Booking::factory()->create(['customer_name' => 'Grace Achieng', 'customer_phone' => '+256772123456', 'reference' => 'LKB-ABCDEFGH']);
        $other = Booking::factory()->create(['customer_name' => 'Peter Okello', 'customer_phone' => '+256701999888']);

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['q' => $term]))
            ->assertSee($match->reference)
            ->assertDontSee($other->reference);
    })->with([
        'name' => 'achieng',
        'reference' => 'lkb-abcd',
        'local phone' => '0772 123 456',
        'partial phone' => '123456',
    ]);

    it('filters by event date range and source', function () {
        $inRange = Booking::factory()->on('2026-12-10')->create(['source' => BookingSource::Phone]);
        $outOfRange = Booking::factory()->on('2027-01-10')->create(['source' => BookingSource::Phone]);
        $wrongSource = Booking::factory()->on('2026-12-11')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['from' => '2026-12-01', 'to' => '2026-12-31', 'source' => 'phone']))
            ->assertSee($inRange->reference)
            ->assertDontSee($outOfRange->reference)
            ->assertDontSee($wrongSource->reference);
    });

    it('flags pending requests whose date is already taken', function () {
        Booking::factory()->approved()->on('2026-12-12')->create();
        Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['status' => 'pending']))
            ->assertSee('Date already taken');
    });

    it('rejects an end date before the start date', function () {
        $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['from' => '2026-12-31', 'to' => '2026-12-01']))
            ->assertSessionHasErrors('to');
    });
});

describe('booking detail', function () {
    it('shows the full booking and its history', function () {
        $booking = Booking::factory()->on('2026-12-12')->create([
            'customer_name' => 'Grace Achieng',
            'customer_email' => 'grace@example.com',
            'event_location' => 'Kyankwanzi Parish Hall',
            'extra_requirements' => 'Vegetarian table for ten',
        ]);
        $history = new BookingStatusHistory;
        $history->booking_id = $booking->id;
        $history->to_status = 'pending';
        $history->save();

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee('Grace Achieng')
            ->assertSee('grace@example.com')
            ->assertSee('Kyankwanzi Parish Hall')
            ->assertSee('Vegetarian table for ten')
            ->assertSee('Request received')
            ->assertSee('https://wa.me/'.ltrim($booking->whatsapp_number, '+'));
    });

    it('warns when a pending request\'s date is already taken', function () {
        Booking::factory()->approved()->on('2026-12-12')->create();
        $pending = Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.show', $pending))
            ->assertViewHas('hasDateConflict', true)
            ->assertSee('This date is already taken');
    });

    it('does not warn when the date is free', function () {
        $pending = Booking::factory()->on('2026-12-12')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.show', $pending))
            ->assertViewHas('hasDateConflict', false);
    });
});

describe('dashboard', function () {
    it('lists requests awaiting a decision, soonest event first', function () {
        $later = Booking::factory()->on('2027-02-01')->create();
        $sooner = Booking::factory()->on('2026-12-01')->create();
        $approved = Booking::factory()->approved()->on('2026-12-05')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder([$sooner->reference, $later->reference])
            ->assertViewHas('pendingBookings', fn ($bookings) => ! $bookings->contains($approved));
    });
});

describe('calendar', function () {
    it('shows approved and pending bookings in the month', function () {
        $approved = Booking::factory()->approved()->on('2026-12-12')->create(['customer_name' => 'Approved Customer']);
        $pending = Booking::factory()->on('2026-12-13')->create(['customer_name' => 'Pending Customer']);
        $rejected = Booking::factory()->rejected()->on('2026-12-14')->create(['customer_name' => 'Rejected Customer']);
        $nextMonth = Booking::factory()->approved()->on('2027-01-02')->create(['customer_name' => 'January Customer']);

        $this->actingAs($this->admin)
            ->get(route('admin.calendar', ['month' => '2026-12']))
            ->assertOk()
            ->assertSee('December 2026')
            ->assertSee('Approved Customer')
            ->assertSee('Pending Customer')
            ->assertDontSee('Rejected Customer')
            ->assertDontSee('January Customer');
    });

    it('can show approved bookings only', function () {
        Booking::factory()->approved()->on('2026-12-12')->create(['customer_name' => 'Approved Customer']);
        Booking::factory()->on('2026-12-13')->create(['customer_name' => 'Pending Customer']);

        $this->actingAs($this->admin)
            ->get(route('admin.calendar', ['month' => '2026-12', 'pending' => 0]))
            ->assertSee('Approved Customer')
            ->assertDontSee('Pending Customer');
    });
});
