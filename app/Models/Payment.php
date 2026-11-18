<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Money received against a booking. Nothing is mass-assignable, and a payment
 * can never be deleted: it is voided with a reason instead.
 */
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'confirmed',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'received_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    /**
     * Payments are kept for the audit trail; deleting one is a bug.
     */
    protected static function booted(): void
    {
        static::deleting(fn () => throw new LogicException('Payments are never deleted. Void the payment instead.'));
    }

    /**
     * Use the receipt number in URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'receipt_number';
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The staff member or admin who recorded the payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * The admin who voided the payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Whether the payment counts towards the booking's balance.
     */
    public function isConfirmed(): bool
    {
        return $this->status === PaymentStatus::Confirmed;
    }

    /**
     * Payments that count towards the amount paid.
     *
     * @param  Builder<Payment>  $query
     */
    #[Scope]
    protected function confirmed(Builder $query): void
    {
        $query->where($query->qualifyColumn('status'), PaymentStatus::Confirmed);
    }
}
