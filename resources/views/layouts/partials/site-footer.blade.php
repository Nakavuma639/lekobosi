<footer class="bg-brand-900 text-brand-100">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div class="lg:col-span-2">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.jpeg') }}" alt="" class="h-14 w-14 rounded-full bg-white object-contain">
                <div>
                    <p class="font-display text-xl font-semibold text-white">Lekobosi</p>
                    <p class="text-sm text-brand-200">For excellent service</p>
                </div>
            </div>
            <p class="mt-6 max-w-md leading-relaxed text-brand-200">
                Restaurant and outside catering from Kyankwanzi, Uganda. We cook and serve
                for weddings, introductions, parties, meetings and every gathering in between.
            </p>
        </div>

        <div>
            <h2 class="font-display text-base font-semibold text-white">Explore</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <li><a href="{{ route('services') }}" class="hover:text-white">Our services</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-white">About us</a></li>
                <li><a href="{{ route('bookings.create') }}" class="hover:text-white">Book an event</a></li>
                <li><a href="{{ route('bookings.track') }}" class="hover:text-white">Track a booking</a></li>
                <li><a href="{{ route('contact.create') }}" class="hover:text-white">Contact us</a></li>
            </ul>
        </div>

        <div>
            <h2 class="font-display text-base font-semibold text-white">Talk to us</h2>
            <ul class="mt-4 space-y-3 text-sm">
                <li>Kyankwanzi, Uganda</li>
                <li><a href="tel:+256775847855" class="hover:text-white">0775 847 855</a></li>
                <li><a href="mailto:Maryharriet07@gmail.com" class="break-all hover:text-white">Maryharriet07@gmail.com</a></li>
                <li>
                    <a href="https://wa.me/256775847855" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-full bg-saffron-500 px-4 py-2 font-semibold text-stone-900 hover:bg-saffron-400">
                        Chat on WhatsApp
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <p class="mx-auto max-w-7xl px-4 py-6 text-xs text-brand-300 sm:px-6 lg:px-8">
            &copy; {{ now()->year }} Lekobosi Restaurant and Outside Catering Services. All rights reserved.
        </p>
    </div>
</footer>
