<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\NotificationStatus;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\NotificationLog;
use App\Notifications\BookingApproved;
use App\Notifications\BookingReceived;
use App\Notifications\BookingRejected;
use App\Notifications\LoggedNotification;
use App\Notifications\NewBookingSubmitted;
use App\Notifications\NewContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Sends Lekobosi's notifications and records every attempt in
 * notification_logs. It never throws: a notification problem must not
 * break a booking, a decision or a contact message (spec §8).
 */
class NotificationDispatcher
{
    public function __construct(private BookingAvailability $availability) {}

    /**
     * A new booking request: alert the owner, acknowledge the customer.
     */
    public function bookingSubmitted(Booking $booking): void
    {
        $this->sendToAdmin(
            new NewBookingSubmitted($booking, $this->availability->isFull($booking->event_date)),
            NotificationLog::EVENT_BOOKING_SUBMITTED,
            $booking,
        );

        $this->sendToCustomer(new BookingReceived($booking), NotificationLog::EVENT_BOOKING_RECEIVED, $booking);
    }

    /**
     * The owner approved, rejected or cancelled a booking: tell the customer.
     */
    public function bookingDecided(Booking $booking, BookingStatus $previousStatus): void
    {
        match ($booking->status) {
            BookingStatus::Approved => $this->sendToCustomer(new BookingApproved($booking), NotificationLog::EVENT_BOOKING_APPROVED, $booking),
            BookingStatus::Rejected => $this->sendToCustomer(
                new BookingRejected($booking, wasCancelled: $previousStatus === BookingStatus::Approved),
                NotificationLog::EVENT_BOOKING_REJECTED,
                $booking,
            ),
            BookingStatus::Pending => null,
        };
    }

    /**
     * A message from the contact form: alert the owner.
     */
    public function contactMessageReceived(ContactMessage $contactMessage): void
    {
        $this->sendToAdmin(new NewContactMessage($contactMessage), NotificationLog::EVENT_CONTACT_MESSAGE);
    }

    private function sendToAdmin(LoggedNotification $notification, string $event, ?Booking $booking = null): void
    {
        $this->send($notification, config('lekobosi.admin.notify_email'), $event, NotificationLog::RECIPIENT_ADMIN, $booking);
    }

    private function sendToCustomer(LoggedNotification $notification, string $event, Booking $booking): void
    {
        $this->send($notification, $booking->customer_email, $event, NotificationLog::RECIPIENT_CUSTOMER, $booking);
    }

    /**
     * Log the attempt, then queue the email. Missing addresses are logged as
     * skipped; dispatch errors are logged as failed and swallowed.
     */
    private function send(LoggedNotification $notification, ?string $email, string $event, string $recipientType, ?Booking $booking): void
    {
        try {
            $log = new NotificationLog;
            $log->booking_id = $booking?->id;
            $log->event = $event;
            $log->recipient_type = $recipientType;
            $log->channel = 'mail';
            $log->recipient = $email;

            if (blank($email)) {
                $log->status = NotificationStatus::Skipped;
                $log->error = $recipientType === NotificationLog::RECIPIENT_ADMIN
                    ? 'ADMIN_NOTIFY_EMAIL is not set.'
                    : 'No email address for this customer.';
                $log->save();

                return;
            }

            $log->status = NotificationStatus::Queued;
            $log->save();

            $notification->notificationLogId = $log->id;

            Notification::route('mail', $email)->notify($notification);
        } catch (Throwable $exception) {
            if (isset($log) && $log->exists) {
                $log->markFailed($exception);
            }

            // Deliberately no recipient or exception message here: both can
            // contain personal data, which must stay out of application logs.
            Log::warning('Notification could not be sent.', [
                'event' => $event,
                'notification_log_id' => isset($log) ? $log->id : null,
                'exception' => $exception::class,
            ]);
        }
    }
}
