<?php

namespace App\Actions\Contact;

use App\Models\ContactMessage;
use App\Services\NotificationDispatcher;

class SubmitContactMessage
{
    public function __construct(private NotificationDispatcher $notifications) {}

    /**
     * Save a message from the public contact form and alert the owner.
     */
    public function handle(string $name, string $email, ?string $phone, ?string $subject, string $message): ContactMessage
    {
        $contactMessage = new ContactMessage;
        $contactMessage->name = $name;
        $contactMessage->email = $email;
        $contactMessage->phone = $phone;
        $contactMessage->subject = $subject;
        $contactMessage->message = $message;
        $contactMessage->is_read = false;
        $contactMessage->save();

        $this->notifications->contactMessageReceived($contactMessage);

        return $contactMessage;
    }
}
