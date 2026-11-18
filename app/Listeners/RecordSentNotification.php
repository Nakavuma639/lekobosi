<?php

namespace App\Listeners;

use App\Models\NotificationLog;
use App\Notifications\LoggedNotification;
use Illuminate\Notifications\Events\NotificationSent;

class RecordSentNotification
{
    /**
     * Mark the matching notification log row as sent.
     */
    public function handle(NotificationSent $event): void
    {
        if (! $event->notification instanceof LoggedNotification || $event->notification->notificationLogId === null) {
            return;
        }

        NotificationLog::query()->find($event->notification->notificationLogId)?->markSent();
    }
}
