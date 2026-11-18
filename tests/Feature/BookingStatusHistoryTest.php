<?php

use App\Enums\BookingStatus;
use App\Models\BookingStatusHistory;

it('refuses to change an existing history row', function () {
    $history = BookingStatusHistory::factory()->create();

    $history->to_status = BookingStatus::Approved;

    expect(fn () => $history->save())->toThrow(LogicException::class, 'append-only');
    expect($history->fresh()->to_status)->toBe(BookingStatus::Pending);
});

it('refuses to delete a history row', function () {
    $history = BookingStatusHistory::factory()->create();

    expect(fn () => $history->delete())->toThrow(LogicException::class, 'append-only');
    expect(BookingStatusHistory::query()->count())->toBe(1);
});
