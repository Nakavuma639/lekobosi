<?php

use App\Support\PhoneNumber;

it('normalises accepted Ugandan formats to E.164', function (string $input) {
    expect(PhoneNumber::normalize($input))->toBe('+256772123456');
})->with([
    'local' => '0772123456',
    'international without plus' => '256772123456',
    'international' => '+256772123456',
    'with spaces' => '0772 123 456',
    'with dashes and brackets' => '(+256) 772-123-456',
]);

it('accepts Airtel and landline prefixes', function (string $input, string $expected) {
    expect(PhoneNumber::normalize($input))->toBe($expected);
})->with([
    'airtel' => ['0752123456', '+256752123456'],
    'kampala landline' => ['0414123456', '+256414123456'],
]);

it('returns null for numbers that are not Ugandan', function (?string $input) {
    expect(PhoneNumber::normalize($input))->toBeNull();
})->with([
    'null' => null,
    'empty' => '',
    'too short' => '077212345',
    'too long' => '07721234567',
    'kenyan' => '+254712345678',
    'letters' => '07721234ab',
    'invalid leading digit' => '0172123456',
]);
