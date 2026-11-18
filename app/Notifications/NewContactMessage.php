<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

/**
 * Tells the owner a message arrived through the contact form.
 */
class NewContactMessage extends LoggedNotification
{
    public function __construct(public ContactMessage $contactMessage)
    {
        parent::__construct();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->contactMessage;

        return (new MailMessage)
            ->subject("New message from {$message->name}".($message->subject ? ": {$message->subject}" : ''))
            ->greeting('New message from the website')
            ->line("**From:** {$message->name} ({$message->email})")
            ->line('**Message:** '.Str::limit($message->message, 500))
            ->action('Read the message', route('admin.messages.show', $message))
            ->salutation('Lekobosi booking system');
    }
}
