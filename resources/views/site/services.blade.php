<x-site-layout title="Our Services | Lekobosi" description="Wedding, introduction, corporate, party and outside catering from Lekobosi in Kyankwanzi, Uganda.">
    <x-site.page-header title="What we cater" :image="asset('images/buffet.jpg')">
        Whatever the occasion, we cook fresh, set up the serving area and stay until the meal is done.
    </x-site.page-header>

    @php
        $featured = [
            [
                'id' => 'weddings',
                'title' => 'Weddings & introductions',
                'image' => 'images/wedding.jpg',
                'alt' => 'A bride and groom at their church wedding',
                'body' => 'Catering for the ceremony and the reception, whether the celebration is a kwanjula at home or a church wedding with hundreds of guests. We plan the menu around your numbers and your family\'s favourites.',
                'points' => ['Buffet or served meals', 'Separate high-table service', 'Drinks and cake table set-up'],
            ],
            [
                'id' => 'outside',
                'title' => 'Outside catering',
                'image' => 'images/lekobosi-buffet-tent.jpg',
                'alt' => 'A Lekobosi buffet tent on a lawn',
                'body' => 'We come to you, at a home, a church compound, a school field or a hired venue. Tents, skirted tables, chafing dishes and plates come with us.',
                'points' => ['Serving tent and tables', 'Hot holding for the whole service', 'Clean-up after the event'],
            ],
        ];

        $services = [
            ['id' => 'corporate', 'title' => 'Corporate events & conferences', 'image' => 'images/corporate.jpg', 'alt' => 'Guests at a conference', 'body' => 'Breakfasts, working lunches and tea breaks for meetings, workshops and conferences.'],
            ['id' => 'parties', 'title' => 'Birthdays & parties', 'image' => 'images/parties.jpg', 'alt' => 'A fruit buffet on party tables', 'body' => 'Food for birthdays, graduations, family gatherings and celebrations of every size.'],
            ['id' => 'buffet', 'title' => 'Buffet service', 'image' => 'images/buffet.jpg', 'alt' => 'Food being served from chafing dishes', 'body' => 'A spread of dishes your guests choose from, kept hot and replenished by our servers.'],
            ['id' => 'serving', 'title' => 'Food preparation & serving', 'image' => 'images/chef-platter.jpg', 'alt' => 'A cook arranging a platter of food', 'body' => 'Breakfast, lunch and dinner, prepared by our cooks and served by our staff.'],
            ['id' => 'snacks', 'title' => 'Snacks & pastries', 'image' => 'images/snacks.jpg', 'alt' => 'Plantain chips and groundnuts in a serving dish', 'body' => 'Cakes, mandazi, chapati, doughnuts, samosas and other snacks for tea breaks and guests.'],
            ['id' => 'dietary', 'title' => 'Special diets', 'image' => 'images/local-dishes.jpg', 'alt' => 'A bowl of cooked greens with posho', 'body' => 'Vegetarian, low-salt and other dietary needs, prepared separately on request.'],
        ];
    @endphp

    <section class="mx-auto max-w-7xl space-y-20 px-4 py-20 sm:px-6 lg:px-8">
        @foreach ($featured as $service)
            <article id="{{ $service['id'] }}" class="grid scroll-mt-28 items-center gap-10 lg:grid-cols-2">
                <img src="{{ asset($service['image']) }}" alt="{{ $service['alt'] }}"
                     @class(['aspect-[4/3] w-full rounded-2xl object-cover', 'lg:order-2' => $loop->odd])>
                <div>
                    <h2 class="text-3xl font-semibold">{{ $service['title'] }}</h2>
                    <p class="mt-4 text-lg leading-relaxed text-stone-600">{{ $service['body'] }}</p>
                    <ul class="mt-6 space-y-3">
                        @foreach ($service['points'] as $point)
                            <li class="flex gap-3">
                                <svg class="mt-0.5 h-6 w-6 shrink-0 text-saffron-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
    </section>

    <section class="bg-stone-50">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-semibold">More of what we do</h2>
            <div class="mt-10 grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <article id="{{ $service['id'] }}" class="scroll-mt-28">
                        <img src="{{ asset($service['image']) }}" alt="{{ $service['alt'] }}" class="aspect-[3/2] w-full rounded-xl object-cover" loading="lazy">
                        <h3 class="mt-5 text-xl font-semibold">{{ $service['title'] }}</h3>
                        <p class="mt-2 leading-relaxed text-stone-600">{{ $service['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('site.partials.cta')
</x-site-layout>
