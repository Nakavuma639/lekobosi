<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Throwable;

/**
 * A record of one notification attempt (who, which channel, and the outcome).
 */
class NotificationLog extends Model
{
    public const EVENT_BOOKING_SUBMITTED = 'booking_submitted';

    public const EVENT_BOOKING_RECEIVED = 'booking_received';

    public const EVENT_BOOKING_APPROVED = 'booking_approved';

    public const EVENT_BOOKING_REJECTED = 'booking_rejected';

    public const EVENT_CONTACT_MESSAGE = 'contact_message';

    public const EVENT_PAYMENT_RECORDED = 'payment_recorded';

    public const RECIPIENT_ADMIN = 'admin';

    public const RECIPIENT_CUSTOMER = 'customer';

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'queued',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NotificationStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Record a successful delivery.
     */
    public function markSent(): void
    {
        $this->status = NotificationStatus::Sent;
        $this->sent_at = now();
        $this->error = null;
        $this->save();
    }

    /**
     * Record a failed delivery. The error is stored here for the owner to see,
     * never in the application log (it can contain the recipient's address).
     */
    public function markFailed(Throwable|string $error): void
    {
        $message = $error instanceof Throwable ? $error::class.': '.$error->getMessage() : $error;

        $this->status = NotificationStatus::Failed;
        $this->error = Str::limit($message, 1000);
        $this->save();
    }

    /**
     * Human-friendly name of the event.
     */
    public function eventLabel(): string
    {
        return match ($this->event) {
            self::EVENT_BOOKING_SUBMITTED => 'New booking alert to the owner',
            self::EVENT_BOOKING_RECEIVED => 'Booking received email to the customer',
            self::EVENT_BOOKING_APPROVED => 'Approval email to the customer',
            self::EVENT_BOOKING_REJECTED => 'Rejection email to the customer',
            self::EVENT_CONTACT_MESSAGE => 'New message alert to the owner',
            self::EVENT_PAYMENT_RECORDED => 'Payment receipt email to the customer',
            default => Str::headline($this->event),
        };
    }
}
