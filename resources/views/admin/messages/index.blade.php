<x-app-layout>
    <x-slot:title>Messages | Lekobosi</x-slot:title>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">Messages</h1>
            <nav class="flex rounded-full border border-stone-300 bg-white p-1 text-sm" aria-label="Filter messages">
                <a href="{{ route('admin.messages.index') }}" @class(['rounded-full px-4 py-1.5 font-medium', 'bg-brand-700 text-white' => ! $unreadOnly, 'text-stone-600' => $unreadOnly]) @if (! $unreadOnly) aria-current="page" @endif>All</a>
                <a href="{{ route('admin.messages.index', ['unread' => 1]) }}" @class(['rounded-full px-4 py-1.5 font-medium', 'bg-brand-700 text-white' => $unreadOnly, 'text-stone-600' => ! $unreadOnly]) @if ($unreadOnly) aria-current="page" @endif>Unread ({{ $unreadCount }})</a>
            </nav>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        @if (session('status'))
            <p class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</p>
        @endif

        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white" aria-label="Messages">
            @if ($messages->isEmpty())
                <div class="px-6 py-14 text-center">
                    <p class="font-medium text-stone-800">{{ $unreadOnly ? 'No unread messages' : 'No messages yet' }}</p>
                    <p class="mt-1 text-stone-500">Messages sent from the website's contact form appear here.</p>
                </div>
            @else
                <ul class="divide-y divide-stone-100">
                    @foreach ($messages as $message)
                        <li>
                            <a href="{{ route('admin.messages.show', $message) }}" class="flex gap-4 px-6 py-4 hover:bg-stone-50">
                                <span @class(['mt-2 h-2.5 w-2.5 shrink-0 rounded-full', 'bg-saffron-500' => ! $message->is_read, 'bg-transparent' => $message->is_read])>
                                    <span class="sr-only">{{ $message->is_read ? 'Read' : 'Unread' }}</span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-baseline justify-between gap-x-4">
                                        <p @class(['truncate text-stone-900', 'font-semibold' => ! $message->is_read, 'font-medium' => $message->is_read])>{{ $message->name }}</p>
                                        <p class="shrink-0 text-xs text-stone-500">{{ $message->created_at->format('j M Y, g:i a') }}</p>
                                    </div>
                                    <p @class(['truncate text-sm', 'font-medium text-stone-800' => ! $message->is_read, 'text-stone-600' => $message->is_read])>{{ $message->subject ?? 'No subject' }}</p>
                                    <p class="truncate text-sm text-stone-500">{{ $message->message }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if ($messages->hasPages())
                    <div class="border-t border-stone-200 px-6 py-4">{{ $messages->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-app-layout>
