<x-guest-layout>
    <x-slot:title>Create an account | Lekobosi</x-slot:title>

    <h1 class="text-3xl font-bold">Create your account</h1>
    <p class="mt-2 text-stone-600">Keep all your bookings, payments and balances in one place.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <x-site.field name="name" label="Full name" autocomplete="name" autofocus required />
        <x-site.field name="email" label="Email address" type="email" autocomplete="username" required />
        <x-site.field name="phone" label="Phone number" type="tel" autocomplete="tel" placeholder="0772 123456" required />
        <x-site.field name="password" label="Password" type="password" autocomplete="new-password" minlength="8" hint="At least 8 characters." required />
        <x-site.field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" minlength="8" required />

        <button type="submit" class="btn-brand w-full py-3.5 text-base">Create account</button>
    </form>

    <p class="mt-8 text-sm text-stone-600">
        Already have an account? <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800">Log in</a>
    </p>
</x-guest-layout>
