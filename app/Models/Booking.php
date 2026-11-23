<?php

namespace App\Models;

use App\Enums\BalanceStatus;
use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Enums\PaymentStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * No attribute is mass-assignable: bookings are always built property by
 * property in an Action, so `reference`, `status` and `decided_by` can never
 * come from request input.
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'source' => 'online',
        'status' => 'pending',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => BookingSource::class,
            'status' => BookingStatus::class,
            'event_type' => EventType::class,
            'guest_count' => 'integer',
            'quoted_amount' => 'integer',
            'status_changed_at' => 'datetime',
        ];
    }

    /**
     * The event date, always stored as a plain Y-m-d string so it can be
     * matched with an indexed `where event_date = ?` on every database.
     *
     * @return Attribute<CarbonImmutable|null, CarbonInterface|string|null>
     */
    protected function eventDate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : CarbonImmutable::parse($value)->startOfDay(),
            set: fn (CarbonInterface|string|null $value) => $value === null ? null : CarbonImmutable::parse($value)->toDateString(),
        );
    }

    /**
     * Use the public reference, never the numeric id, in URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    /**
     * The customer account the booking belongs to, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The staff member or admin who entered the booking on the customer's behalf.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The admin who approved or rejected the booking.
     *
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Every status change, oldest first.
     *
     * @return HasMany<BookingStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class)->oldest('id');
    }

    /**
     * Every payment, oldest first, including voided ones.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('received_at')->orderBy('id');
    }

    /**
     * Total of confirmed payments, in UGX. Uses the `amount_paid` value from
     * withAmountPaid() when the query loaded it, so lists avoid N+1 queries.
     */
    public function amountPaid(): int
    {
        if (array_key_exists('amount_paid', $this->attributes)) {
            return (int) $this->attributes['amount_paid'];
        }

        if ($this->relationLoaded('payments')) {
            return (int) $this->payments->where('status', PaymentStatus::Confirmed)->sum('amount');
        }

        return (int) $this->payments()->confirmed()->sum('amount');
    }

    /**
     * Quote minus confirmed payments; null until a quote is set.
     */
    public function balance(): ?int
    {
        return $this->quoted_amount === null ? null : $this->quoted_amount - $this->amountPaid();
    }

    /**
     * Unpaid, part paid or paid in full; null until a quote is set.
     */
    public function balanceStatus(): ?BalanceStatus
    {
        return $this->quoted_amount === null ? null : BalanceStatus::for($this->quoted_amount, $this->amountPaid());
    }

    /**
     * Whether staff may record a payment: approved and quoted (spec §6.4).
     */
    public function acceptsPayments(): bool
    {
        return $this->status === BookingStatus::Approved && $this->quoted_amount !== null;
    }

    /**
     * Load the confirmed payment total as `amount_paid`.
     *
     * @param  Builder<Booking>  $query
     */
    #[Scope]
    protected function withAmountPaid(Builder $query): void
    {
        $query->withSum(['payments as amount_paid' => fn (Builder $payments) => $payments->confirmed()], 'amount');
    }

    /**
     * Notification attempts about this booking, newest first.
     *
     * @return HasMany<NotificationLog, $this>
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class)->latest('id');
    }

    /**
     * Bookings that hold their date.
     *
     * @param  Builder<Booking>  $query
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where($query->qualifyColumn('status'), BookingStatus::Approved);
    }

    /**
     * Pending requests received recently enough to count as "new"
     * (lekobosi.bookings.new_request_days).
     *
     * @param  Builder<Booking>  $query
     */
    #[Scope]
    protected function newRequests(Builder $query): void
    {
        $query->where($query->qualifyColumn('status'), BookingStatus::Pending)
            ->where($query->qualifyColumn('created_at'), '>=', now()->subDays(config('lekobosi.bookings.new_request_days')));
    }
}
