<?php

use App\Models\Booking;
use App\Models\User;

describe('dashboard redirect', function () {
    it('sends each role to its own dashboard', function (string $state, string $routeName) {
        $user = User::factory()->{$state}()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route($routeName));
    })->with([
        'admin' => ['admin', 'admin.dashboard'],
        'staff' => ['staff', 'admin.dashboard'],
    ]);

    it('sends customers to their account page', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('customer.dashboard'));
    });
});

describe('back-office area', function () {
    it('redirects guests to the login page', function () {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    });

    it('returns 403 to customers', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    });

    it('lets admins and staff in', function (string $state) {
        $this->actingAs(User::factory()->{$state}()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Awaiting your decision');
    })->with(['admin', 'staff']);

    it('shows live booking totals', function () {
        $this->travelTo('2026-11-18 10:00:00');
        Booking::factory()->count(3)->create();
        Booking::factory()->approved()->on('2026-12-01')->create();
        Booking::factory()->approved()->on('2027-03-01')->create();
        Booking::factory()->rejected()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertViewHas('totals', [
                'total' => 6,
                'pending' => 3,
                'approved' => 2,
                'rejected' => 1,
                'upcoming_30_days' => 1,
            ]);
    });
});

describe('customer area', function () {
    it('shows the customer their own details', function () {
        $customer = User::factory()->create(['phone' => '+256772123456']);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee($customer->email)
            ->assertSee('+256772123456');
    });

    it('returns 403 to back-office users', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('customer.dashboard'))
            ->assertForbidden();
    });
});

describe('customer bookings', function () {
    it('lists only the signed-in customer\'s own bookings', function () {
        $customer = User::factory()->create();
        $mine = Booking::factory()->forCustomer($customer)->create();
        $theirs = Booking::factory()->forCustomer(User::factory()->create())->create();

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertSee($mine->reference)
            ->assertDontSee($theirs->reference);
    });
});
