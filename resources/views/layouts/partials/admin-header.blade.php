<header class="header">
    <a href="{{ route('home') }}" class="logo">
        <img src="{{ asset('images/logo.jpeg') }}" alt="Lekobosi Restaurant Logo">
    </a>

    <div>
        <h2 style="color:white; margin:0;">Admin Dashboard</h2>
        <p style="margin:0;">Lekobosi Restaurant and Outside Catering Services</p>
    </div>

    <nav>
        <a href="{{ route('home') }}">View Website</a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); this.closest('form').submit();">Logout</a>
        </form>
    </nav>
</header>
