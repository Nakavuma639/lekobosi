<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config()->set('lekobosi.admin.seed', [
        'name' => 'Owner Example',
        'email' => 'Owner@Example.com',
        'password' => 'a-long-secret',
    ]);
});

it('creates an active admin from the seed configuration', function () {
    $this->artisan('lekobosi:create-admin')->assertSuccessful();

    $admin = User::query()->where('email', 'owner@example.com')->sole();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and($admin->is_active)->toBeTrue()
        ->and(Hash::check('a-long-secret', $admin->password))->toBeTrue();
});

it('promotes an existing account instead of creating a duplicate', function () {
    User::factory()->inactive()->create(['email' => 'owner@example.com']);

    $this->artisan('lekobosi:create-admin')->assertSuccessful();

    $admin = User::query()->where('email', 'owner@example.com')->sole();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and($admin->is_active)->toBeTrue();
});

it('fails without creating anyone when the seed password is missing', function () {
    config()->set('lekobosi.admin.seed.password', null);

    $this->artisan('lekobosi:create-admin')
        ->expectsOutputToContain('Set ADMIN_SEED_NAME, ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD')
        ->assertFailed();

    expect(User::query()->count())->toBe(0);
});
