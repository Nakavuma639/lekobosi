@php
    use App\Enums\BookingStatus;
    $whatsappDigits = ltrim($booking->whatsapp_number ?? $booking->customer_phone, '+');
@endphp

<x-app-layout>
    <x-slot:title>{{ $booking->reference }} | Lekobosi</x-slot:title>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm text-stone-500 hover:text-brand-700">All bookings</a>
                <h1 class="mt-1 flex flex-wrap items-center gap-3 text-2xl font-semibold">
                    <span class="font-mono tracking-wide">{{ $booking->reference }}</span>
                    <x-booking-status :status="$booking->status" />
                </h1>
            </div>
            <p class="text-sm text-stone-500">Received {{ $booking->created_at->format('j M Y, g:i a') }} &middot; {{ $booking->source->label() }}</p>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div class="space-y-6 lg:col-span-2">
            @if (session('status'))
                <p class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</p>
            @endif

            @error('status')
                <p class="rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800" role="alert">{{ $message }}</p>
            @enderror

            @if ($hasDateConflict)
                <div class="rounded-xl border border-saffron-300 bg-saffron-50 px-5 py-4" role="alert">
                    <p class="font-semibold text-stone-900">This date is already taken</p>
                    <p class="mt-1 text-sm text-stone-700">Another booking on {{ $booking->event_date->format('j F Y') }} has been approved, so this request can't be approved unless that one is cancelled. You may want to reject it with a note suggesting another date.</p>
                </div>
            @endif

            <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="event-heading">
                <h2 id="event-heading" class="border-b border-stone-200 px-6 py-4 text-lg font-semibold">Event</h2>
                <dl class="grid gap-6 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-stone-500">Type</dt>
                        <dd class="mt-1 font-medium text-stone-900">{{ $booking->event_type->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">Date</dt>
                        <dd class="mt-1 font-medium text-stone-900">{{ $booking->event_date->format('l, j F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">Location</dt>
                        <dd class="mt-1 font-medium text-stone-900">{{ $booking->event_location }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">Guests</dt>
                        <dd class="mt-1 font-medium text-stone-900">{{ number_format($booking->guest_count) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-stone-500">Additional requirements</dt>
                        <dd class="mt-1 whitespace-pre-line text-stone-800">{{ $booking->extra_requirements ?? 'None given.' }}</dd>
                    </div>
                </dl>
            </section>

            @include('admin.bookings.partials.payments')

            <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="customer-heading">
                <h2 id="customer-heading" class="border-b border-stone-200 px-6 py-4 text-lg font-semibold">Customer</h2>
                <dl class="grid gap-6 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-stone-500">Name</dt>
                        <dd class="mt-1 font-medium text-stone-900">{{ $booking->customer_name }}</dd>
                        <dd class="mt-1 text-xs text-stone-500">{{ $booking->user ? 'Has a customer account' : 'No account (guest booking)' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">Email</dt>
                        <dd class="mt-1 break-all font-medium">
                            @if ($booking->customer_email)
                                <a href="mailto:{{ $booking->customer_email }}" class="text-brand-700 hover:text-brand-800">{{ $booking->customer_email }}</a>
                            @else
                                <span class="text-stone-500">Not given</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">Phone</dt>
                        <dd class="mt-1 font-medium"><a href="tel:{{ $booking->customer_phone }}" class="text-brand-700 hover:text-brand-800">{{ $booking->customer_phone }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">WhatsApp</dt>
                        <dd class="mt-1 font-medium"><a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener" class="text-brand-700 hover:text-brand-800">{{ $booking->whatsapp_number ?? $booking->customer_phone }}</a></dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="history-heading">
                <h2 id="history-heading" class="border-b border-stone-200 px-6 py-4 text-lg font-semibold">History</h2>
                <ol class="space-y-5 px-6 py-6">
                    @forelse ($booking->statusHistories as $history)
                        <li class="flex gap-4">
                            <span @class([
                                'mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full',
                                'bg-saffron-500' => $history->to_status === BookingStatus::Pending,
                                'bg-emerald-600' => $history->to_status === BookingStatus::Approved,
                                'bg-brand-700' => $history->to_status === BookingStatus::Rejected,
                            ])></span>
                            <div>
                                <p class="font-medium text-stone-900">
                                    @if ($history->from_status === null)
                                        Request received
                                    @elseif ($history->from_status === BookingStatus::Approved && $history->to_status === BookingStatus::Rejected)
                                        Cancelled after approval
                                    @else
                                        {{ $history->to_status->label() }}
                                    @endif
                                </p>
                                <p class="text-sm text-stone-500">
                                    {{ $history->created_at->format('j M Y, g:i a') }}
                                    &middot; {{ $history->changer?->name ?? ($booking->source === \App\Enums\BookingSource::Online ? 'Customer, via the website' : 'System') }}
                                </p>
                                @if ($history->note)
                                    <p class="mt-1 text-sm text-stone-700">"{{ $history->note }}"</p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-stone-500">No status changes recorded for this booking.</li>
                    @endforelse
                </ol>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="decision-heading">
                <h2 id="decision-heading" class="text-lg font-semibold">Decision</h2>

                @if (! $canDecide)
                    <p class="mt-3 text-sm text-stone-600">Only the owner can approve or reject bookings.</p>
                @elseif ($booking->status === BookingStatus::Pending)
                    <form method="POST" action="{{ route('admin.bookings.status.update', $booking) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <div>
                            <label for="quoted_amount" class="field-label">Quote in UGX <span class="font-normal text-stone-500">(optional)</span></label>
                            <input id="quoted_amount" name="quoted_amount" type="text" inputmode="numeric" value="{{ old('quoted_amount') }}" placeholder="e.g. 4,500,000" class="field-input">
                            @error('quoted_amount') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="btn w-full bg-emerald-700 text-white hover:bg-emerald-800" @disabled($hasDateConflict)>Approve booking</button>
                        @if ($hasDateConflict)
                            <p class="text-xs text-stone-500">Approval is unavailable while the date is taken.</p>
                        @endif
                    </form>

                    <form method="POST" action="{{ route('admin.bookings.status.update', $booking) }}" class="mt-6 space-y-4 border-t border-stone-200 pt-6">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <div>
                            <label for="reject_note" class="field-label">Reason for rejecting</label>
                            <textarea id="reject_note" name="note" rows="3" maxlength="500" required class="field-input" placeholder="The customer sees this when they track the booking.">{{ old('note') }}</textarea>
                            @error('note') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="btn-outline w-full border-brand-300 text-brand-800 hover:bg-brand-50">Reject booking</button>
                    </form>
                @elseif ($booking->status === BookingStatus::Approved)
                    <p class="mt-3 text-sm text-stone-600">Approved {{ $booking->status_changed_at?->format('j M Y') }}{{ $booking->decider ? ' by '.$booking->decider->name : '' }}.</p>
                    <details class="mt-5 border-t border-stone-200 pt-5" @if ($errors->has('note')) open @endif>
                        <summary class="cursor-pointer text-sm font-semibold text-brand-700">Cancel this booking</summary>
                        <form method="POST" action="{{ route('admin.bookings.status.update', $booking) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <div>
                                <label for="cancel_note" class="field-label">Reason for cancelling</label>
                                <textarea id="cancel_note" name="note" rows="3" maxlength="500" required class="field-input" placeholder="The customer sees this when they track the booking.">{{ old('note') }}</textarea>
                                @error('note') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="btn-outline w-full border-brand-300 text-brand-800 hover:bg-brand-50">Cancel booking and free the date</button>
                        </form>
                    </details>
                @else
                    <p class="mt-3 text-sm text-stone-600">Rejected {{ $booking->status_changed_at?->format('j M Y') }}{{ $booking->decider ? ' by '.$booking->decider->name : '' }}. Rejected bookings are final; the customer can send a new request.</p>
                @endif
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="whatsapp-heading">
                <h2 id="whatsapp-heading" class="text-lg font-semibold">WhatsApp the customer</h2>
                <p class="mt-2 text-sm text-stone-600">Opens WhatsApp with this message ready to send:</p>
                <blockquote class="mt-3 rounded-lg bg-stone-50 p-3 text-sm leading-relaxed text-stone-700">{{ $whatsappMessage }}</blockquote>
                <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="btn mt-4 w-full bg-[#25D366] text-stone-900 hover:bg-[#1ebe5b]">Send on WhatsApp</a>
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="notifications-heading">
                <h2 id="notifications-heading" class="text-lg font-semibold">Emails</h2>
                @if ($booking->notificationLogs->isEmpty())
                    <p class="mt-3 text-sm text-stone-600">No emails have been sent about this booking.</p>
                @else
                    <ul class="mt-3 space-y-3">
                        @foreach ($booking->notificationLogs as $log)
                            <li class="text-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-stone-800">{{ $log->eventLabel() }}</span>
                                    <span @class([
                                        'shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-800' => $log->status === \App\Enums\NotificationStatus::Sent,
                                        'bg-saffron-100 text-saffron-700' => $log->status === \App\Enums\NotificationStatus::Queued,
                                        'bg-brand-100 text-brand-800' => $log->status === \App\Enums\NotificationStatus::Failed,
                                        'bg-stone-100 text-stone-600' => $log->status === \App\Enums\NotificationStatus::Skipped,
                                    ])>{{ $log->status->label() }}</span>
                                </div>
                                <p class="text-xs text-stone-500">{{ ($log->sent_at ?? $log->created_at)->format('j M Y, g:i a') }}</p>
                                @if ($log->status === \App\Enums\NotificationStatus::Skipped && $log->error)
                                    <p class="text-xs text-stone-500">{{ $log->error }}</p>
                                @elseif ($log->status === \App\Enums\NotificationStatus::Failed)
                                    <p class="text-xs text-brand-700">Could not be delivered. Use WhatsApp or call the customer instead.</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-6" aria-labelledby="same-date-heading">
                <h2 id="same-date-heading" class="text-lg font-semibold">Same date</h2>
                @if ($otherBookingsOnDate->isEmpty())
                    <p class="mt-3 text-sm text-stone-600">No other active bookings on {{ $booking->event_date->format('j F Y') }}.</p>
                @else
                    <ul class="mt-3 divide-y divide-stone-100">
                        @foreach ($otherBookingsOnDate as $other)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <a href="{{ route('admin.bookings.show', $other) }}" class="min-w-0 text-sm">
                                    <span class="block truncate font-medium text-stone-900">{{ $other->customer_name }}</span>
                                    <span class="font-mono text-xs text-stone-500">{{ $other->reference }}</span>
                                </a>
                                <x-booking-status :status="$other->status" class="text-xs" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </div>
</x-app-layout>
