<x-site-layout title="Contact | Lekobosi">
    <x-site.page-header title="Get in touch">
        Questions about a menu, a date or a quote? Call, send a WhatsApp message, or use the form.
    </x-site.page-header>

    <section class="mx-auto grid max-w-7xl gap-16 px-4 py-16 sm:px-6 lg:grid-cols-5 lg:px-8">
        <div class="lg:col-span-2">
            <dl class="space-y-8">
                <div>
                    <dt class="text-sm text-stone-500">Call us</dt>
                    <dd class="mt-1 font-display text-2xl font-semibold"><a href="tel:+256775847855" class="hover:text-brand-700">0775 847 855</a></dd>
                </div>
                <div>
                    <dt class="text-sm text-stone-500">WhatsApp</dt>
                    <dd class="mt-2"><a href="https://wa.me/256775847855" target="_blank" rel="noopener" class="btn-primary">Chat on WhatsApp</a></dd>
                </div>
                <div>
                    <dt class="text-sm text-stone-500">Email</dt>
                    <dd class="mt-1 break-all text-lg font-medium"><a href="mailto:Maryharriet07@gmail.com" class="hover:text-brand-700">Maryharriet07@gmail.com</a></dd>
                </div>
                <div>
                    <dt class="text-sm text-stone-500">Where we are</dt>
                    <dd class="mt-1 text-lg font-medium">Kyankwanzi, Uganda</dd>
                    <dd class="mt-1 text-sm text-stone-600">We cater at venues across the region.</dd>
                </div>
            </dl>
        </div>

        {{-- Wired to contact.store in Phase 5. --}}
        <form action="#" method="post" class="space-y-6 rounded-2xl border border-stone-200 p-6 sm:p-10 lg:col-span-3" novalidate>
            @csrf
            <h2 class="text-2xl font-semibold">Send us a message</h2>

            <div class="grid gap-6 sm:grid-cols-2">
                <x-site.field name="name" label="Your name" autocomplete="name" required />
                <x-site.field name="email" label="Email address" type="email" autocomplete="email" required />
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <x-site.field name="phone" label="Phone number" type="tel" autocomplete="tel" placeholder="0772 123456" />
                <x-site.field name="subject" label="Subject" maxlength="150" />
            </div>
            <x-site.field name="message" label="Message" type="textarea" rows="6" minlength="10" maxlength="2000" required />

            <button type="submit" class="btn-brand px-8 py-3.5 text-base">Send message</button>
        </form>
    </section>
</x-site-layout>
