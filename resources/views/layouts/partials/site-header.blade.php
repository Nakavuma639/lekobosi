@php
    $links = [
        ['route' => 'home', 'label' => 'Home'],
        ['route' => 'services', 'label' => 'Services'],
        ['route' => 'about', 'label' => 'About us'],
        ['route' => 'contact.create', 'label' => 'Contact'],
        ['route' => 'bookings.track', 'label' => 'Track a booking'],
    ];
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-stone-200/80 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Lekobosi" class="h-14 w-14 rounded-full object-contain">
            <span class="hidden leading-tight sm:block">
                <span class="block font-display text-lg font-semibold text-brand-700">Lekobosi</span>
                <span class="block text-xs text-stone-500">Restaurant &amp; Outside Catering</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @class([
                       'rounded-full px-4 py-2 text-sm font-medium transition-colors',
                       'bg-brand-50 text-brand-700' => request()->routeIs($link['route']),
                       'text-stone-600 hover:text-brand-700' => ! request()->routeIs($link['route']),
                   ])>{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-stone-700 hover:text-brand-700">My dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-stone-500 hover:text-brand-700">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-stone-700 hover:text-brand-700">Log in</a>
            @endauth
            <a href="{{ route('bookings.create') }}" class="btn-brand">Book an event</a>
        </div>

        <button type="button" class="inline-flex items-center justify-center rounded-md p-2 text-stone-700 lg:hidden"
                @click="open = ! open" :aria-expanded="open.toString()" aria-controls="mobile-menu">
            <span class="sr-only">Open menu</span>
            <svg x-show="! open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    <div id="mobile-menu" x-show="open" x-cloak x-transition.opacity class="border-t border-stone-200 bg-white lg:hidden">
        <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-4 sm:px-6" aria-label="Mobile">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @class([
                       'rounded-lg px-3 py-2.5 font-medium',
                       'bg-brand-50 text-brand-700' => request()->routeIs($link['route']),
                       'text-stone-700' => ! request()->routeIs($link['route']),
                   ])>{{ $link['label'] }}</a>
            @endforeach

            <div class="mt-3 flex flex-col gap-2 border-t border-stone-200 pt-4">
                <a href="{{ route('bookings.create') }}" class="btn-brand">Book an event</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-outline">My dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-outline w-full">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-outline">Log in</a>
                    <a href="{{ route('register') }}" class="btn-outline">Create an account</a>
                @endauth
            </div>
        </nav>
    </div>
</header>
