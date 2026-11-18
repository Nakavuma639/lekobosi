<?php

use App\Enums\BookingStatus;

it('allows only the transitions in the booking lifecycle', function (BookingStatus $from, BookingStatus $to, bool $allowed) {
    expect($from->canTransitionTo($to))->toBe($allowed);
})->with([
    'pending to approved' => [BookingStatus::Pending, BookingStatus::Approved, true],
    'pending to rejected' => [BookingStatus::Pending, BookingStatus::Rejected, true],
    'approved to rejected (cancellation)' => [BookingStatus::Approved, BookingStatus::Rejected, true],
    'pending to pending' => [BookingStatus::Pending, BookingStatus::Pending, false],
    'approved to approved' => [BookingStatus::Approved, BookingStatus::Approved, false],
    'approved back to pending' => [BookingStatus::Approved, BookingStatus::Pending, false],
    'rejected to approved' => [BookingStatus::Rejected, BookingStatus::Approved, false],
    'rejected to pending' => [BookingStatus::Rejected, BookingStatus::Pending, false],
    'rejected to rejected' => [BookingStatus::Rejected, BookingStatus::Rejected, false],
]);
