<?php

namespace App\Actions\Contact;

use App\Models\ContactMessage;

class SubmitContactMessage
{
    /**
     * Save a message from the public contact form for the owner to review.
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

        return $contactMessage;
    }
}
