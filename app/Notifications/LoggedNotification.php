<?php

namespace App\Notifications;

use App\Models\NotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Throwable;

/**
 * Base for every Lekobosi notification: queued, sent only after the database
 * transaction commits, retried a few times, and tracked in notification_logs.
 */
abstract class LoggedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Attempts before the notification is given up as failed.
     */
    public int $tries = 3;

    /**
     * Seconds to wait between attempts.
     *
     * @var list<int>
     */
    public array $backoff = [60, 300];

    /**
     * The notification_logs row this delivery updates.
     */
    public ?int $notificationLogId = null;

    public function __construct()
    {
        $this->afterCommit();
    }

    /**
     * Email for now; WhatsApp can be added here once the owner has an account.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Called by the queue after the last attempt fails.
     */
    public function failed(Throwable $exception): void
    {
        if ($this->notificationLogId !== null) {
            NotificationLog::query()->find($this->notificationLogId)?->markFailed($exception);
        }
    }
}
