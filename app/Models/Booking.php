<?php

namespace App\Models;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\EventType;
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
     * Bookings that hold their date.
     *
     * @param  Builder<Booking>  $query
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', BookingStatus::Approved);
    }
}
