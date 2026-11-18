<x-site-layout title="About Us | Lekobosi">
    <x-site.page-header title="A family kitchen that grew into an events team">
        Lekobosi Restaurant and Outside Catering Services is based in Kyankwanzi, Uganda.
        We run a restaurant and take our kitchen to events across the region.
    </x-site.page-header>

    <section class="mx-auto grid max-w-7xl gap-12 px-4 py-20 sm:px-6 lg:grid-cols-5 lg:px-8">
        <div class="lg:col-span-3">
            <img src="{{ asset('images/chef-platter.jpg') }}" alt="A cook arranging a platter of food" class="aspect-[16/10] w-full rounded-2xl object-cover">
        </div>
        <div class="space-y-10 lg:col-span-2">
            <div>
                <h2 class="text-2xl font-semibold">Our mission</h2>
                <p class="mt-3 leading-relaxed text-stone-600">To provide reliable catering services and a good customer experience.</p>
            </div>
            <div>
                <h2 class="text-2xl font-semibold">Our vision</h2>
                <p class="mt-3 leading-relaxed text-stone-600">To become a trusted catering service provider for different occasions.</p>
            </div>
            <div>
                <h2 class="text-2xl font-semibold">Our commitment</h2>
                <p class="mt-3 leading-relaxed text-stone-600">To improve booking follow-up, date confirmation and payment records, so you always know where your event stands.</p>
            </div>
        </div>
    </section>

    <section class="border-y border-stone-200 bg-stone-50">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold">What we hold ourselves to</h2>
            <dl class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    'Quality' => 'Fresh ingredients, cooked the same day they are served.',
                    'Integrity' => 'Clear quotes and honest records of every payment.',
                    'Customer care' => 'One point of contact from your first call to the last plate.',
                    'Reliability' => 'We arrive early and we don\'t leave your guests waiting.',
                ] as $value => $meaning)
                    <div class="border-l-2 border-saffron-500 pl-5">
                        <dt class="font-display text-lg font-semibold text-stone-900">{{ $value }}</dt>
                        <dd class="mt-2 text-sm leading-relaxed text-stone-600">{{ $meaning }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-semibold">Meet the team</h2>
        <div class="mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['name' => 'Namagembe Harriet', 'role' => 'Head Chef and CEO', 'image' => 'images/team-harriet.jpg', 'phone' => '+256775847855', 'display' => '+256 775 847 855', 'email' => 'Maryharriet07@gmail.com'],
                ['name' => 'Nakavuma Phionah', 'role' => 'Manager', 'image' => 'images/team-phionah.jpg', 'phone' => '+256775150674', 'display' => '+256 775 150 674', 'email' => 'phionahnakavuma1@gmail.com'],
                ['name' => 'Kakumba Joseph', 'role' => 'Events Coordinator', 'image' => 'images/team-joseph.jpg', 'phone' => '+256753132455', 'display' => '+256 753 132 455', 'email' => null],
            ] as $member)
                <article>
                    <img src="{{ asset($member['image']) }}" alt="{{ $member['name'] }}" class="aspect-[4/5] w-full rounded-2xl object-cover object-top" loading="lazy">
                    <h3 class="mt-5 text-xl font-semibold">{{ $member['name'] }}</h3>
                    <p class="text-brand-700">{{ $member['role'] }}</p>
                    <ul class="mt-3 space-y-1 text-sm text-stone-600">
                        <li><a href="tel:{{ $member['phone'] }}" class="hover:text-brand-700">{{ $member['display'] }}</a></li>
                        <li><a href="https://wa.me/{{ ltrim($member['phone'], '+') }}" target="_blank" rel="noopener" class="hover:text-brand-700">WhatsApp</a></li>
                        @if ($member['email'])
                            <li><a href="mailto:{{ $member['email'] }}" class="break-all hover:text-brand-700">{{ $member['email'] }}</a></li>
                        @endif
                    </ul>
                </article>
            @endforeach
        </div>
    </section>

    <section class="bg-stone-900">
        <dl class="mx-auto grid max-w-7xl gap-8 px-4 py-14 text-center sm:grid-cols-3 sm:px-6 lg:px-8">
            <div><dt class="text-sm text-stone-400">Events served</dt><dd class="mt-1 font-display text-4xl font-bold text-saffron-400">500+</dd></div>
            <div><dt class="text-sm text-stone-400">Happy customers</dt><dd class="mt-1 font-display text-4xl font-bold text-saffron-400">1,000+</dd></div>
            <div><dt class="text-sm text-stone-400">Years of experience</dt><dd class="mt-1 font-display text-4xl font-bold text-saffron-400">5+</dd></div>
        </dl>
    </section>

    @include('site.partials.cta')
</x-site-layout>
