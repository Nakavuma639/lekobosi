<?php

use App\Actions\Payments\RecordPayment;
use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    useMysqlLockingDatabase($this);

    $this->travelTo('2026-11-18 10:00:00');
    $this->staff = User::factory()->staff()->create();
    $this->booking = Booking::factory()->approved()->create(['quoted_amount' => 1_000_000]);
});

afterEach(fn () => closeMysqlLockingConnections());

it('waits while another payment on the same booking is being recorded', function () {
    $other = DB::connection('mysql_lock_test_other');
    $other->beginTransaction();
    $other->table('bookings')->where('id', $this->booking->id)->lockForUpdate()->first();

    DB::connection('mysql_lock_test')->statement('SET SESSION innodb_lock_wait_timeout = 1');

    expect(fn () => app(RecordPayment::class)->handle($this->booking, 600_000, PaymentMethod::Cash, CarbonImmutable::now(), $this->staff))
        ->toThrow(QueryException::class, 'Lock wait timeout');

    expect(Payment::query()->count())->toBe(0);
});

it('refuses the second of two payments that together exceed the quote', function () {
    app(RecordPayment::class)->handle($this->booking, 600_000, PaymentMethod::Cash, CarbonImmutable::now(), $this->staff);

    expect(fn () => app(RecordPayment::class)->handle($this->booking, 600_000, PaymentMethod::Cash, CarbonImmutable::now(), $this->staff))
        ->toThrow(ValidationException::class);

    expect(Payment::query()->sum('amount'))->toEqual(600_000);
});
