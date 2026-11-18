@php
    $replySubject = rawurlencode('Re: '.($contactMessage->subject ?? 'Your message to Lekobosi'));
@endphp

<x-app-layout>
    <x-slot:title>Message from {{ $contactMessage->name }} | Lekobosi</x-slot:title>

    <x-slot name="header">
        <a href="{{ route('admin.messages.index') }}" class="text-sm text-stone-500 hover:text-brand-700">All messages</a>
        <h1 class="mt-1 text-2xl font-semibold">{{ $contactMessage->subject ?? 'No subject' }}</h1>
    </x-slot>

    <div class="mx-auto grid max-w-5xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
        <article class="rounded-xl border border-stone-200 bg-white p-6 lg:col-span-2">
            <p class="text-sm text-stone-500">Received {{ $contactMessage->created_at->format('l, j F Y \a\t g:i a') }}</p>
            <div class="mt-4 whitespace-pre-line leading-relaxed text-stone-800">{{ $contactMessage->message }}</div>
        </article>

        <aside class="space-y-6">
            <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="sender-heading">
                <h2 id="sender-heading" class="text-lg font-semibold">From</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-stone-500">Name</dt>
                        <dd class="font-medium text-stone-900">{{ $contactMessage->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Email</dt>
                        <dd class="break-all font-medium"><a href="mailto:{{ $contactMessage->email }}?subject={{ $replySubject }}" class="text-brand-700 hover:text-brand-800">{{ $contactMessage->email }}</a></dd>
                    </div>
                    @if ($contactMessage->phone)
                        <div>
                            <dt class="text-stone-500">Phone</dt>
                            <dd class="font-medium">
                                <a href="tel:{{ $contactMessage->phone }}" class="text-brand-700 hover:text-brand-800">{{ $contactMessage->phone }}</a>
                                &middot; <a href="https://wa.me/{{ ltrim($contactMessage->phone, '+') }}" target="_blank" rel="noopener" class="text-brand-700 hover:text-brand-800">WhatsApp</a>
                            </dd>
                        </div>
                    @endif
                </dl>
                <a href="mailto:{{ $contactMessage->email }}?subject={{ $replySubject }}" class="btn-brand mt-6 w-full">Reply by email</a>
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="read-heading">
                <h2 id="read-heading" class="text-lg font-semibold">Status</h2>
                @if ($contactMessage->is_read)
                    <p class="mt-3 text-sm text-stone-600">Marked as read {{ $contactMessage->read_at?->format('j M Y, g:i a') }}{{ $contactMessage->reader ? ' by '.$contactMessage->reader->name : '' }}.</p>
                @elseif ($canMarkAsRead)
                    <p class="mt-3 text-sm text-stone-600">Not yet marked as read.</p>
                    <form method="POST" action="{{ route('admin.messages.read', $contactMessage) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-outline w-full">Mark as read</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-stone-600">Unread. The owner marks messages as read once they've been handled.</p>
                @endif
            </section>
        </aside>
    </div>
</x-app-layout>
