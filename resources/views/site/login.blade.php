<x-guest-layout>
    <x-slot:title>Log in | Lekobosi</x-slot:title>

    <h1 class="text-3xl font-bold">Welcome back</h1>
    <p class="mt-2 text-stone-600">Log in to see your bookings and payments.</p>

    @if (session('status'))
        <p class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <x-site.field name="email" label="Email address" type="email" autocomplete="username" autofocus required />

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="field-label">Password</label>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-700 hover:text-brand-800">Forgot password?</a>
            </div>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="field-input">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <label for="remember_me" class="flex items-center gap-2 text-sm text-stone-600">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-stone-300 text-brand-700 focus:ring-brand-600">
            Keep me logged in on this device
        </label>

        <button type="submit" class="btn-brand w-full py-3.5 text-base">Log in</button>
    </form>

    <p class="mt-8 text-sm text-stone-600">
        New here? <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:text-brand-800">Create a customer account</a>
    </p>
    <p class="mt-2 text-sm text-stone-500">Staff use the account the owner set up for them.</p>
</x-guest-layout>
