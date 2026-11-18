<?php

namespace App\Http\Controllers;

use App\Actions\Contact\SubmitContactMessage;
use App\Http\Requests\StoreContactMessageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Show the contact page and form.
     */
    public function create(): View
    {
        return view('site.contact');
    }

    /**
     * Save a contact message and confirm it was sent.
     */
    public function store(StoreContactMessageRequest $request, SubmitContactMessage $submitContactMessage): RedirectResponse
    {
        $submitContactMessage->handle(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            subject: $request->validated('subject'),
            message: $request->validated('message'),
        );

        return redirect()
            ->route('contact.create')
            ->with('contact_sent', 'Thank you. Your message has been sent and we will get back to you soon.');
    }
}
