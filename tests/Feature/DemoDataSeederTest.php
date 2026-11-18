<?php

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;

it('seeds a usable demo with bookings, payments and accounts in every state', function () {
    $this->seed(DemoDataSeeder::class);

    expect(User::query()->where('role', UserRole::Admin)->count())->toBe(1)
        ->and(User::query()->where('role', UserRole::Staff)->count())->toBe(1)
        ->and(Booking::query()->distinct()->pluck('status')->map->value->sort()->values()->all())->toBe(['approved', 'pending', 'rejected'])
        ->and(Payment::query()->where('status', 'voided')->count())->toBe(1)
        ->and(Booking::query()->get()->every(fn (Booking $booking) => $booking->balance() === null || $booking->balance() >= 0))->toBeTrue();

    $this->actingAs(User::query()->where('email', 'owner@demo.lekobosi.test')->sole())
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('refuses to run in production', function () {
    app()->detectEnvironment(fn () => 'production');

    expect(fn () => app(DemoDataSeeder::class)->run())->toThrow(RuntimeException::class, 'must not run in production');
    expect(User::query()->count())->toBe(0);
});
