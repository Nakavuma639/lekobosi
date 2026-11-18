<?php

namespace App\Models;

use App\Enums\EventType;
use Database\Factories\BookingConflictAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A booking request turned away because its date was already full.
 */
class BookingConflictAttempt extends Model
{
    /** @use HasFactory<BookingConflictAttemptFactory> */
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
            'event_type' => EventType::class,
            'requested_date' => 'date',
            'created_at' => 'datetime',
        ];
    }
}
