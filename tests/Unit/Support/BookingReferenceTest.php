<?php

use App\Support\BookingReference;

it('generates references in the LKB-XXXXXXXX format', function () {
    expect(BookingReference::generate())->toMatch('/^LKB-[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{8}$/');
});

it('never uses characters that are easy to misread', function () {
    $characters = implode('', array_map(fn () => substr(BookingReference::generate(), 4), range(1, 200)));

    expect($characters)->not->toMatch('/[01OIL]/');
});

it('does not repeat references across many generations', function () {
    $references = array_map(fn () => BookingReference::generate(), range(1, 1000));

    expect(array_unique($references))->toHaveCount(1000);
});

it('normalises typed references to trimmed uppercase', function () {
    expect(BookingReference::normalize('  lkb-7k3qx9md '))->toBe('LKB-7K3QX9MD');
});
