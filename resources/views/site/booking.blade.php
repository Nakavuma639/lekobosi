<x-site-layout title="Book an Event | Lekobosi">
    <x-site.page-header title="Request a booking">
        Tell us about your event. The owner reviews every request and gets back to you with a decision.
    </x-site.page-header>

    <section class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-3 lg:px-8">
        <aside class="lg:order-2">
            <div class="rounded-2xl bg-stone-50 p-8 lg:sticky lg:top-28">
                <h2 class="text-xl font-semibold">What happens next</h2>
                <ol class="mt-6 space-y-5 text-sm">
                    <li class="flex gap-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-700 text-xs font-semibold text-white">1</span>
                        <span class="leading-relaxed">You get a booking reference as soon as you send this form. Keep it to track your request.</span>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-700 text-xs font-semibold text-white">2</span>
                        <span class="leading-relaxed">We check the date and approve or decline the request. Your date isn't held until it's approved.</span>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-700 text-xs font-semibold text-white">3</span>
                        <span class="leading-relaxed">We contact you to agree the menu, the quote and the deposit.</span>
                    </li>
                </ol>
                <div class="mt-8 border-t border-stone-200 pt-6 text-sm">
                    <p class="text-stone-600">Prefer to talk first?</p>
                    <a href="https://wa.me/256775847855" target="_blank" rel="noopener" class="mt-2 inline-block font-semibold text-brand-700 hover:text-brand-800">WhatsApp 0775 847 855</a>
                </div>
            </div>
        </aside>

        <form action="{{ route('bookings.store') }}" method="post" class="space-y-10 lg:col-span-2" novalidate>
            @csrf

            @if ($errors->any())
                <div class="rounded-xl border border-brand-200 bg-brand-50 px-5 py-4" role="alert">
                    <p class="font-semibold text-brand-800">Please check the highlighted fields.</p>
                    @error('date')
                        <p class="mt-1 text-sm text-brand-800">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Honeypot: hidden from people, often filled in by spam bots. --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Leave this field empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <fieldset class="space-y-6">
                <legend class="font-display text-xl font-semibold text-stone-900">Your details</legend>
                <x-site.field name="fullname" label="Full name" autocomplete="name" :value="$customer?->name" required />
                <div class="grid gap-6 sm:grid-cols-2">
                    <x-site.field name="email" label="Email address" type="email" autocomplete="email" :value="$customer?->email" required />
                    <x-site.field name="phone" label="Phone number" type="tel" autocomplete="tel" placeholder="0772 123456" :value="$customer?->phone" required />
                </div>
                <x-site.field name="whatsapp" label="WhatsApp number" type="tel" placeholder="0772 123456"
                              hint="We send booking updates here. Leave it blank if it's the same as your phone number." />
            </fieldset>

            <fieldset class="space-y-6">
                <legend class="w-full border-t border-stone-200 pt-10 font-display text-xl font-semibold text-stone-900">Your event</legend>
                <div class="grid gap-6 sm:grid-cols-2">
                    <x-site.field name="event_type" label="Type of event" type="select" required>
                        <option value="">Choose one</option>
                        @foreach ($eventTypes as $eventType)
                            <option value="{{ $eventType->value }}" @selected(old('event_type') === $eventType->value)>{{ $eventType->label() }}</option>
                        @endforeach
                    </x-site.field>

                    <div x-data="{
                            full: false,
                            async check(value) {
                                this.full = false;
                                if (! value) return;
                                try {
                                    const response = await fetch('{{ route('availability') }}?month=' + value.slice(0, 7), { headers: { Accept: 'application/json' } });
                                    if (! response.ok) return;
                                    const data = await response.json();
                                    this.full = data.full_dates.includes(value);
                                } catch (error) {}
                            },
                         }">
                        <x-site.field name="date" label="Event date" type="date" :min="$earliestDate" :max="$latestDate"
                                      x-on:change="check($event.target.value)" required />
                        <p x-show="full" x-cloak class="field-error" role="status">That date is already booked. Please choose another date.</p>
                    </div>
                </div>
                <div class="grid gap-6 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <x-site.field name="location" label="Event location" placeholder="Venue, village or town" required />
                    </div>
                    <x-site.field name="guests" label="Number of guests" type="number" min="1" :max="$maxGuests" inputmode="numeric" required />
                </div>
                <x-site.field name="details" label="Anything else we should know" type="textarea" maxlength="2000"
                              placeholder="Menu ideas, dietary needs, timings, tent or seating requirements" />
            </fieldset>

            <div class="flex flex-col gap-4 border-t border-stone-200 pt-8 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-stone-500">We'll only use your details to arrange this booking.</p>
                <button type="submit" class="btn-brand px-8 py-3.5 text-base">Send booking request</button>
            </div>
        </form>
    </section>
</x-site-layout>
