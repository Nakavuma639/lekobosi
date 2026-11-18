<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';
    case Skipped = 'skipped';

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Waiting to send',
            self::Sent => 'Sent',
            self::Failed => 'Failed',
            self::Skipped => 'Not sent',
        };
    }
}
