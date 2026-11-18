<x-site-layout title="Track a Booking | Lekobosi">
    <x-site.page-header title="Track your booking">
        Enter the reference you received when you sent your request, for example <span class="whitespace-nowrap">LKB-7K3QX9MD</span>.
    </x-site.page-header>

    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <form action="{{ route('bookings.track') }}" method="get" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="reference" class="field-label">Booking reference</label>
                <input id="reference" name="reference" type="text" value="{{ $reference }}" required maxlength="20"
                       autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="LKB-XXXXXXXX"
                       @if ($notFoundMessage) aria-invalid="true" aria-describedby="reference-error" @endif
                       class="field-input py-3 font-medium uppercase tracking-wider placeholder:normal-case placeholder:tracking-normal">
            </div>
            <button type="submit" class="btn-brand py-3.5">Check status</button>
        </form>

        @if ($notFoundMessage)
            <div id="reference-error" class="mt-6 rounded-xl border border-brand-200 bg-brand-50 px-5 py-4" role="alert">
                <p class="font-medium text-brand-800">{{ $notFoundMessage }}</p>
                <p class="mt-1 text-sm text-brand-800/80">References look like LKB-7K3QX9MD. Letters O, I and L and the digits 0 and 1 are never used.</p>
            </div>
        @endif

        @if ($trackedBooking)
            <section class="mt-8 overflow-hidden rounded-2xl border border-stone-200" aria-labelledby="result-heading" data-testid="tracking-result">
                <div class="flex flex-wrap items-center justify-between gap-4 bg-stone-50 px-6 py-5">
                    <div>
                        <p class="text-sm text-stone-500">Booking</p>
                        <h2 id="result-heading" class="font-display text-2xl font-bold tracking-wider">{{ $trackedBooking['reference'] }}</h2>
                    </div>
                    <x-booking-status :status="$trackedBooking['status']" class="px-4 py-1.5 text-base" />
                </div>

                <dl class="grid gap-6 px-6 py-6 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm text-stone-500">Event</dt>
                        <dd class="mt-1 font-medium text-stone-900">{{ $trackedBooking['event_type']->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">Event date</dt>
                        <dd class="mt-1 font-medium text-stone-900">{{ $trackedBooking['event_date']->format('l, j F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-500">Last updated</dt>
                        <dd class="mt-1 font-medium text-stone-900">{{ $trackedBooking['status_changed_at']?->format('j M Y, g:i a') ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="border-t border-stone-200 px-6 py-5">
                    @switch($trackedBooking['status'])
                        @case(\App\Enums\BookingStatus::Pending)
                            <p class="leading-relaxed text-stone-700">Your request is waiting for the owner's review. Your date isn't held until it's approved, so check back soon.</p>
                            @break
                        @case(\App\Enums\BookingStatus::Approved)
                            <p class="leading-relaxed text-stone-700">Your date is confirmed. We'll contact you to agree the menu, the quote and the deposit.</p>
                            @break
                        @case(\App\Enums\BookingStatus::Rejected)
                            @if ($trackedBooking['decision_note'])
                                <p class="text-sm font-medium text-stone-500">Reason given</p>
                                <p class="mt-1 leading-relaxed text-stone-800">{{ $trackedBooking['decision_note'] }}</p>
                            @endif
                            <p class="mt-3 leading-relaxed text-stone-700">You're welcome to <a href="{{ route('bookings.create') }}" class="font-semibold text-brand-700 hover:text-brand-800">request another date</a> or talk to us on WhatsApp.</p>
                            @break
                    @endswitch
                </div>
            </section>
        @endif

        <div class="mt-16">
            <h2 class="text-xl font-semibold">What each status means</h2>
            <dl class="mt-6 divide-y divide-stone-200 border-y border-stone-200">
                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:gap-8">
                    <dt class="w-32 shrink-0"><span class="inline-flex rounded-full bg-saffron-100 px-3 py-1 text-sm font-semibold text-saffron-700">Pending</span></dt>
                    <dd class="leading-relaxed text-stone-600">We have your request and the owner hasn't decided yet. The date isn't held for you until it's approved.</dd>
                </div>
                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:gap-8">
                    <dt class="w-32 shrink-0"><span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">Approved</span></dt>
                    <dd class="leading-relaxed text-stone-600">Your date is confirmed. We'll contact you to agree the menu and payment.</dd>
                </div>
                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:gap-8">
                    <dt class="w-32 shrink-0"><span class="inline-flex rounded-full bg-brand-100 px-3 py-1 text-sm font-semibold text-brand-800">Rejected</span></dt>
                    <dd class="leading-relaxed text-stone-600">We can't cater for this request. The reason is shown with your status. Contact us to find another date.</dd>
                </div>
            </dl>
        </div>

        <div class="mt-12 flex flex-col items-start gap-4 rounded-2xl bg-stone-50 p-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold">Lost your reference?</h2>
                <p class="mt-1 text-stone-600">Send us your name and event date on WhatsApp and we'll look it up.</p>
            </div>
            <a href="https://wa.me/256775847855" target="_blank" rel="noopener" class="btn-outline shrink-0">Message us on WhatsApp</a>
        </div>
    </section>
</x-site-layout>
