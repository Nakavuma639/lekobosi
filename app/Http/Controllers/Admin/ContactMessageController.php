<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    /**
     * The contact inbox, newest first, optionally unread only.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ContactMessage::class);

        $unreadOnly = $request->boolean('unread');

        $messages = ContactMessage::query()
            ->when($unreadOnly, fn ($query) => $query->unread())
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $unreadCount = ContactMessage::query()->unread()->count();

        return view('admin.messages.index', compact('messages', 'unreadOnly', 'unreadCount'));
    }

    /**
     * Read one message.
     */
    public function show(Request $request, ContactMessage $contactMessage): View
    {
        Gate::authorize('view', $contactMessage);

        $contactMessage->load('reader');
        $canMarkAsRead = $request->user()->can('markAsRead', $contactMessage);

        return view('admin.messages.show', compact('contactMessage', 'canMarkAsRead'));
    }

    /**
     * Mark a message as read (owner only).
     */
    public function markAsRead(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        Gate::authorize('markAsRead', $contactMessage);

        $contactMessage->markAsReadBy($request->user());

        return redirect()
            ->route('admin.messages.index')
            ->with('status', "Message from {$contactMessage->name} marked as read.");
    }
}
