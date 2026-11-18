<?php

use App\Models\ContactMessage;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Owner Harriet']);
});

it('returns 403 to customers', function () {
    $message = ContactMessage::factory()->create();
    $customer = User::factory()->create();

    $this->actingAs($customer)->get(route('admin.messages.index'))->assertForbidden();
    $this->actingAs($customer)->get(route('admin.messages.show', $message))->assertForbidden();
    $this->actingAs($customer)->patch(route('admin.messages.read', $message))->assertForbidden();
});

it('lists messages newest first', function () {
    $older = ContactMessage::factory()->create(['name' => 'Older Sender', 'created_at' => now()->subDay()]);
    $newer = ContactMessage::factory()->create(['name' => 'Newer Sender']);

    $this->actingAs($this->admin)
        ->get(route('admin.messages.index'))
        ->assertOk()
        ->assertSeeInOrder(['Newer Sender', 'Older Sender']);
});

it('can show unread messages only', function () {
    ContactMessage::factory()->create(['name' => 'Unread Sender']);
    ContactMessage::factory()->read()->create(['name' => 'Read Sender']);

    $this->actingAs($this->admin)
        ->get(route('admin.messages.index', ['unread' => 1]))
        ->assertSee('Unread Sender')
        ->assertDontSee('Read Sender')
        ->assertSee('Unread (1)');
});

it('lets staff read messages but not mark them read', function () {
    $message = ContactMessage::factory()->create(['message' => 'Can you do a buffet for 200?']);
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)->get(route('admin.messages.index'))->assertOk();
    $this->actingAs($staff)
        ->get(route('admin.messages.show', $message))
        ->assertOk()
        ->assertSee('Can you do a buffet for 200?')
        ->assertDontSee('Mark as read');

    $this->actingAs($staff)->patch(route('admin.messages.read', $message))->assertForbidden();

    expect($message->refresh()->is_read)->toBeFalse();
});

it('does not mark a message read just by opening it', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->admin)->get(route('admin.messages.show', $message))->assertOk();

    expect($message->refresh()->is_read)->toBeFalse();
});

it('lets the owner mark a message as read and records who and when', function () {
    $this->travelTo('2026-11-18 15:30:00');
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.messages.read', $message))
        ->assertRedirect(route('admin.messages.index'));

    $message->refresh();
    expect($message->is_read)->toBeTrue()
        ->and($message->read_at->toDateTimeString())->toBe('2026-11-18 15:30:00')
        ->and($message->read_by)->toBe($this->admin->id);

    $this->actingAs($this->admin)
        ->get(route('admin.messages.show', $message))
        ->assertSee('by Owner Harriet');
});

it('counts unread messages on the dashboard', function () {
    ContactMessage::factory()->count(2)->create();
    ContactMessage::factory()->read()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertViewHas('unreadMessages', 2);
});
