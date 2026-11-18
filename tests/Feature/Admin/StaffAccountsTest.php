<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Owner Harriet']);
});

it('lets the owner add a staff member, who is emailed a link to set their password', function () {
    Notification::fake();

    $this->actingAs($this->admin)
        ->post(route('admin.staff.store'), ['name' => 'Joseph Kakumba', 'email' => 'Joseph@Example.com', 'phone' => '0753 132 455'])
        ->assertRedirect(route('admin.staff.index'))
        ->assertSessionHas('status', 'Account created. Joseph Kakumba has been emailed a link to set their password.');

    $staff = User::query()->where('email', 'joseph@example.com')->sole();

    expect($staff->role)->toBe(UserRole::Staff)
        ->and($staff->is_active)->toBeTrue()
        ->and($staff->phone)->toBe('+256753132455')
        ->and(Hash::check('password', $staff->password))->toBeFalse();

    Notification::assertSentTo($staff, ResetPassword::class);
});

it('refuses an email that already has an account', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($this->admin)
        ->post(route('admin.staff.store'), ['name' => 'Someone', 'email' => 'taken@example.com', 'phone' => '0772123456'])
        ->assertSessionHasErrors(['email' => 'Someone already has an account with this email.']);
});

it('disables a staff account and signs them out everywhere', function () {
    $staff = User::factory()->staff()->create(['remember_token' => 'old-token']);
    DB::table('sessions')->insert(['id' => 'staff-session', 'user_id' => $staff->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => '', 'last_activity' => now()->timestamp]);
    config()->set('session.driver', 'database');

    $this->actingAs($this->admin)
        ->patch(route('admin.staff.update', $staff), ['is_active' => 0])
        ->assertSessionHas('status', "{$staff->name}'s account is disabled and they have been signed out.");

    $staff->refresh();
    expect($staff->is_active)->toBeFalse()
        ->and($staff->remember_token)->not->toBe('old-token')
        ->and(DB::table('sessions')->where('user_id', $staff->id)->count())->toBe(0);
});

it('signs out a disabled staff member on their next request', function () {
    $staff = User::factory()->staff()->inactive()->create();

    $this->actingAs($staff)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Your account has been disabled. Please speak to the owner.');

    $this->assertGuest();
});

it('can enable a disabled account again', function () {
    $staff = User::factory()->staff()->inactive()->create();

    $this->actingAs($this->admin)->patch(route('admin.staff.update', $staff), ['is_active' => 1]);

    expect($staff->refresh()->is_active)->toBeTrue();
});

it('does not let the owner disable an admin account, including their own', function () {
    $this->actingAs($this->admin)->patch(route('admin.staff.update', $this->admin), ['is_active' => 0])->assertForbidden();

    expect($this->admin->refresh()->is_active)->toBeTrue();
});

it('resends a password link', function () {
    Notification::fake();
    $staff = User::factory()->staff()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.staff.password-link', $staff))
        ->assertSessionHas('status', "A password link has been emailed to {$staff->name}.");

    Notification::assertSentTo($staff, ResetPassword::class);
});

it('keeps staff management to the owner', function () {
    $staff = User::factory()->staff()->create();
    $other = User::factory()->staff()->create();

    $this->actingAs($staff)->get(route('admin.staff.index'))->assertForbidden();
    $this->actingAs($staff)->get(route('admin.staff.create'))->assertForbidden();
    $this->actingAs($staff)->patch(route('admin.staff.update', $other), ['is_active' => 0])->assertForbidden();

    expect($other->refresh()->is_active)->toBeTrue();
});

it('lists staff and admins but not customers', function () {
    User::factory()->staff()->create(['name' => 'Staff Person']);
    User::factory()->create(['name' => 'Customer Person']);

    $this->actingAs($this->admin)
        ->get(route('admin.staff.index'))
        ->assertSee('Staff Person')
        ->assertSee('Owner Harriet')
        ->assertDontSee('Customer Person');
});

it('explains when a password link was sent less than a minute ago', function () {
    Notification::fake();
    $staff = User::factory()->staff()->create();

    $this->actingAs($this->admin)->post(route('admin.staff.password-link', $staff));

    $this->actingAs($this->admin)
        ->post(route('admin.staff.password-link', $staff))
        ->assertSessionHas('status', "A link was sent to {$staff->name} less than a minute ago. Ask them to check their inbox and spam folder, or try again shortly.");
});
