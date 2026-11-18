<header class="header">
    <a href="{{ route('home') }}" class="logo">
        <img src="{{ asset('images/logo.jpeg') }}" alt="Lekobosi logo">
    </a>
    <button class="menu-toggle">☰</button>

    <nav>
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('about') }}">About Us</a>
        <a href="{{ route('services') }}">Services</a>
        <a href="{{ route('bookings.create') }}">Booking</a>
        <a href="{{ route('contact.create') }}">Contact</a>
        <a href="{{ route('bookings.track') }}">Track Bookings</a>
    </nav>

    <div>
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="button">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="button">Login</a>
            <a href="{{ route('register') }}" class="button">Register</a>
        @endauth
    </div>
</header>
