@php
    $user = Auth::user();
    $navLinks = $user->isBackOffice()
        ? [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
            ['route' => 'admin.bookings.index', 'label' => 'Bookings', 'active' => 'admin.bookings.*', 'badge' => $newBookingRequests ?? 0],
            ['route' => 'admin.payments.index', 'label' => 'Payments'],
            ['route' => 'admin.calendar', 'label' => 'Calendar'],
            ['route' => 'admin.messages.index', 'label' => 'Messages', 'active' => 'admin.messages.*'],
            ['route' => 'admin.reports.bookings', 'label' => 'Reports', 'active' => 'admin.reports.*'],
            ...($user->hasRole(\App\Enums\UserRole::Admin) ? [['route' => 'admin.staff.index', 'label' => 'Staff', 'active' => 'admin.staff.*']] : []),
        ]
        : [['route' => 'customer.dashboard', 'label' => 'My bookings'], ['route' => 'bookings.create', 'label' => 'Book an event']];
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-stone-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <x-application-logo class="block h-11 w-11" />
                        <span class="hidden font-display text-lg font-semibold text-brand-700 md:block">Lekobosi</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 lg:-my-px lg:ms-10 lg:flex">
                    @foreach ($navLinks as $link)
                        <x-nav-link :href="route($link['route'])" :active="request()->routeIs($link['active'] ?? $link['route'])">
                            {{ $link['label'] }}
                            <x-nav-badge :count="$link['badge'] ?? 0" class="ms-1.5" />
                        </x-nav-link>
                    @endforeach
                    <x-nav-link :href="route('home')">
                        {{ __('View website') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden lg:flex lg:items-center lg:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="text-right">
                                <div class="text-stone-800">{{ $user->name }}</div>
                                <div class="text-xs font-normal text-stone-500">{{ $user->role->label() }}</div>
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($navLinks as $link)
                <x-responsive-nav-link :href="route($link['route'])" :active="request()->routeIs($link['active'] ?? $link['route'])">
                    {{ $link['label'] }}
                    <x-nav-badge :count="$link['badge'] ?? 0" class="ms-2" />
                </x-responsive-nav-link>
            @endforeach
            <x-responsive-nav-link :href="route('home')">
                {{ __('View website') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ $user->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ $user->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
