<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Append-only: a history row can be created but never changed or deleted.
 */
class BookingStatusHistory extends Model
{
    /** @use HasFactory<BookingStatusHistoryFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => BookingStatus::class,
            'to_status' => BookingStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * Block updates and deletes so the audit trail cannot be rewritten.
     */
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Booking status history is append-only.'));
        static::deleting(fn () => throw new LogicException('Booking status history is append-only.'));
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The user who made the change; null means the customer or the system.
     *
     * @return BelongsTo<User, $this>
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
