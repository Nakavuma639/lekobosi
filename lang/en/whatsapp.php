<?php

/*
|--------------------------------------------------------------------------
| WhatsApp message templates
|--------------------------------------------------------------------------
|
| Pre-filled text for the "Send on WhatsApp" button on a booking. Staff
| review the message in WhatsApp and tap send. Edit the wording freely;
| keep the :placeholders.
|
*/

return [
    'pending' => 'Hello :name, thank you for your booking request with Lekobosi. Your reference is :reference for your :event on :date. We will let you know our decision soon.',
    'approved' => 'Hello :name, good news! Your :event on :date (ref :reference) is confirmed. We will contact you to agree the menu and the deposit. Lekobosi Restaurant and Outside Catering',
    'rejected' => "Hello :name, we're sorry but we can't cater for your :event on :date (ref :reference). Reason: :reason. Please contact us and we'll help you find another date. Lekobosi Restaurant and Outside Catering",
];
