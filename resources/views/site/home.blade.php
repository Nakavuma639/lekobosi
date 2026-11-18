<x-site-layout title="Lekobosi Restaurant & Outside Catering | Kyankwanzi, Uganda">
    {{-- Hero: the one bold moment on the page. --}}
    <section class="relative isolate overflow-hidden bg-stone-900">
        <img src="{{ asset('images/hero-garden-event.jpg') }}" alt="A dessert and cake table set up on a lawn beside white event tents"
             class="absolute inset-0 -z-10 h-full w-full object-cover">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-stone-950/90 via-stone-950/70 to-stone-950/10"></div>

        <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8 lg:py-40">
            <div class="max-w-2xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm text-saffron-200 ring-1 ring-white/20">
                    Outside catering from Kyankwanzi
                </p>
                <h1 class="mt-6 text-4xl font-bold leading-[1.05] text-white sm:text-6xl">
                    Food for the days your family will talk about for years
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-stone-200">
                    Weddings, introductions, birthdays and meetings. We bring the tent, the buffet
                    and a team that cooks and serves until the last guest has eaten.
                </p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="{{ route('bookings.create') }}" class="btn-primary px-7 py-3.5 text-base">Request a booking</a>
                    <a href="{{ route('services') }}" class="btn-on-dark px-7 py-3.5 text-base">See what we cater</a>
                </div>
            </div>
        </div>
    </section>

    {{-- How booking works: a real sequence, so numbered. --}}
    <section class="border-b border-stone-200 bg-stone-50">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold sm:text-3xl">How booking works</h2>
            <ol class="mt-10 grid gap-10 md:grid-cols-3">
                <li class="flex gap-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-700 font-display font-semibold text-white">1</span>
                    <div>
                        <h3 class="text-lg font-semibold">Send your request</h3>
                        <p class="mt-2 leading-relaxed text-stone-600">Tell us the date, the place and how many guests. You get a booking reference straight away.</p>
                    </div>
                </li>
                <li class="flex gap-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-700 font-display font-semibold text-white">2</span>
                    <div>
                        <h3 class="text-lg font-semibold">We confirm the date</h3>
                        <p class="mt-2 leading-relaxed text-stone-600">The owner reviews every request personally and lets you know if the date is yours.</p>
                    </div>
                </li>
                <li class="flex gap-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-700 font-display font-semibold text-white">3</span>
                    <div>
                        <h3 class="text-lg font-semibold">Agree the menu, enjoy the day</h3>
                        <p class="mt-2 leading-relaxed text-stone-600">We settle the quote and deposit with you, then arrive early to set up and serve.</p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    {{-- Services preview: one large, two small. --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-xl">
                <h2 class="text-3xl font-semibold sm:text-4xl">Catering for every kind of gathering</h2>
                <p class="mt-4 text-lg leading-relaxed text-stone-600">From a hundred guests at a church wedding to a working lunch for a board meeting.</p>
            </div>
            <a href="{{ route('services') }}" class="btn-outline">All services</a>
        </div>

        <div class="mt-12 grid gap-6 lg:grid-cols-5 lg:grid-rows-2">
            <a href="{{ route('services') }}#weddings" class="group relative overflow-hidden rounded-2xl lg:col-span-3 lg:row-span-2">
                <img src="{{ asset('images/wedding.jpg') }}" alt="A bride and groom at their church wedding" class="h-80 w-full object-cover transition duration-500 group-hover:scale-105 lg:h-full">
                <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 via-stone-950/10 to-transparent"></div>
                <div class="absolute bottom-0 p-6 sm:p-8">
                    <h3 class="text-2xl font-semibold text-white sm:text-3xl">Weddings &amp; introductions</h3>
                    <p class="mt-2 max-w-md text-stone-200">Full buffet service for the ceremony and reception, sized to your guest list.</p>
                </div>
            </a>

            <a href="{{ route('services') }}#corporate" class="group relative overflow-hidden rounded-2xl lg:col-span-2">
                <img src="{{ asset('images/corporate.jpg') }}" alt="Guests seated at a conference" class="h-64 w-full object-cover transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 to-transparent"></div>
                <div class="absolute bottom-0 p-6">
                    <h3 class="text-xl font-semibold text-white">Corporate events &amp; conferences</h3>
                </div>
            </a>

            <a href="{{ route('services') }}#parties" class="group relative overflow-hidden rounded-2xl lg:col-span-2">
                <img src="{{ asset('images/parties.jpg') }}" alt="A fruit buffet with skewers on a party table" class="h-64 w-full object-cover transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 to-transparent"></div>
                <div class="absolute bottom-0 p-6">
                    <h3 class="text-xl font-semibold text-white">Birthdays &amp; parties</h3>
                </div>
            </a>
        </div>
    </section>

    {{-- Their own setup: real proof. --}}
    <section class="bg-stone-50">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8">
            <img src="{{ asset('images/lekobosi-buffet-tent.jpg') }}" alt="A Lekobosi buffet tent set up on a lawn with chafing dishes and stacked plates"
                 class="aspect-[4/5] w-full rounded-2xl object-cover sm:aspect-[4/3] lg:aspect-[4/5]">
            <div>
                <h2 class="text-3xl font-semibold sm:text-4xl">We bring the whole setup</h2>
                <p class="mt-5 text-lg leading-relaxed text-stone-600">
                    That's one of our buffet tents, ready before the guests arrive. Skirted serving tables,
                    hot chafing dishes, plates and cutlery. You choose the venue, and we turn it into a dining hall.
                </p>
                <ul class="mt-8 space-y-4">
                    @foreach (['Tent, tables and serving equipment', 'Cooks and serving staff on the day', 'Local favourites and special diets on request'] as $point)
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-6 w-6 shrink-0 text-saffron-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-stone-700">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('about') }}" class="btn-outline mt-10">Meet the team</a>
            </div>
        </div>
    </section>

    @include('site.partials.cta')
</x-site-layout>
