<x-site-layout title="Track a Booking | Lekobosi">
    <x-site.page-header title="Track your booking">
        Enter the reference you received when you sent your request, for example LKB-7K3QX9MD.
    </x-site.page-header>

    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <form action="{{ route('bookings.track') }}" method="get" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="reference" class="field-label">Booking reference</label>
                <input id="reference" name="reference" type="text" value="{{ request('reference') }}" required
                       autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="LKB-XXXXXXXX"
                       class="field-input py-3 font-medium uppercase tracking-wider placeholder:normal-case placeholder:tracking-normal">
            </div>
            <button type="submit" class="btn-brand py-3.5">Check status</button>
        </form>

        {{-- The status result appears here in Phase 3. --}}

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
