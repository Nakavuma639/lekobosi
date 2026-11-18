@php
    $editing = $booking !== null;
    $value = fn (string $field, mixed $default = null) => old($field, $default);
@endphp

<x-app-layout>
    <x-slot:title>{{ $editing ? "Edit {$booking->reference}" : 'New booking' }} | Lekobosi</x-slot:title>

    <x-slot name="header">
        <a href="{{ $editing ? route('admin.bookings.show', $booking) : route('admin.bookings.index') }}" class="text-sm text-stone-500 hover:text-brand-700">
            {{ $editing ? "Back to {$booking->reference}" : 'All bookings' }}
        </a>
        <h1 class="mt-1 text-2xl font-semibold">{{ $editing ? 'Edit booking details' : 'New phone or walk-in booking' }}</h1>
        @unless ($editing)
            <p class="mt-1 text-sm text-stone-500">For customers who called or came in. It goes into the list as Pending, like a website booking.</p>
        @endunless
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <form method="POST" action="{{ $editing ? route('admin.bookings.update', $booking) : route('admin.bookings.store') }}" class="space-y-8 rounded-xl border border-stone-200 bg-white p-6 sm:p-8" novalidate>
            @csrf
            @if ($editing)
                @method('PATCH')
            @endif

            @if ($errors->any())
                <p class="rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800" role="alert">
                    {{ $errors->first('date') ?: 'Please check the highlighted fields.' }}
                </p>
            @endif

            @unless ($editing)
                <fieldset>
                    <legend class="field-label">How did the booking come in?</legend>
                    <div class="mt-2 flex flex-wrap gap-3">
                        @foreach ($sources as $source)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-stone-300 px-4 py-2.5 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="source" value="{{ $source->value }}" @checked(old('source', 'phone') === $source->value) class="text-brand-700 focus:ring-brand-600">
                                <span class="text-sm font-medium">{{ $source->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('source') <p class="field-error">{{ $message }}</p> @enderror
                </fieldset>
            @endunless

            <fieldset class="space-y-5">
                <legend class="font-display text-lg font-semibold text-stone-900">Customer</legend>
                <x-site.field name="fullname" label="Full name" :value="$booking?->customer_name" required />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-site.field name="phone" label="Phone number" type="tel" placeholder="0772 123456" :value="$booking?->customer_phone" required />
                    <x-site.field name="whatsapp" label="WhatsApp number" type="tel" placeholder="Same as phone if blank" :value="$booking?->whatsapp_number" />
                </div>
                <x-site.field name="email" label="Email address" type="email" :value="$booking?->customer_email"
                              hint="Leave blank if the customer has no email. They won't get email updates; use WhatsApp instead." />
            </fieldset>

            <fieldset class="space-y-5">
                <legend class="w-full border-t border-stone-200 pt-8 font-display text-lg font-semibold text-stone-900">Event</legend>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-site.field name="event_type" label="Type of event" type="select" required>
                        <option value="">Choose one</option>
                        @foreach ($eventTypes as $eventType)
                            <option value="{{ $eventType->value }}" @selected($value('event_type', $booking?->event_type->value) === $eventType->value)>{{ $eventType->label() }}</option>
                        @endforeach
                    </x-site.field>
                    <x-site.field name="date" label="Event date" type="date" :min="$earliestDate" :max="$latestDate" :value="$booking?->event_date->toDateString()" required />
                </div>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <x-site.field name="location" label="Event location" :value="$booking?->event_location" required />
                    </div>
                    <x-site.field name="guests" label="Number of guests" type="number" min="1" inputmode="numeric" :value="$booking?->guest_count" required />
                </div>
                <x-site.field name="details" label="Additional requirements" type="textarea" maxlength="2000" :value="$booking?->extra_requirements" />
            </fieldset>

            <div class="flex flex-wrap justify-end gap-3 border-t border-stone-200 pt-6">
                <a href="{{ $editing ? route('admin.bookings.show', $booking) : route('admin.bookings.index') }}" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-brand">{{ $editing ? 'Save changes' : 'Create booking' }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
