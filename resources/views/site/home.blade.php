<x-site-layout title="Home | Lekobosi Resturant and outside Catering services">
<section class="hero">
    <div class="container">
        <p class="eyebrow">LEKOBOSI RESTAURANT & CATERING SERVICES</p>
        <h1>Making Your Special <span>Moments Memorable</span></h1>
        <p>
            Quality food and reliable catering services for weddings,
            parties, meetings and other special occasions.
        </p>
        <a href="{{ route('bookings.create') }}" class="button">Book Our Services</a>
        <a href="{{ route('services') }}" class="button">Explore Services</a>
    </div>
</section>

<section class="section">
    <p class="eyebrow">WELCOME TO LEKOBOSI</p>
    <h2>Great Food, Beautiful Moments</h2>
    <p>
        We provide catering services for different events and occasions.
        Our website helps customers request bookings and follow up on
        their event arrangements.
    </p>
    <a href="{{ route('about') }}" class="button">Learn About Us</a>
</section>

<section class="section">
    <p class="eyebrow">WHAT WE OFFER</p>
    <h2>Our Catering Services</h2>

    <div class="cards">
        <article class="card">
            <img src="{{ asset('images/image2.jpg') }}" alt="Wedding catering">
            <h3>Wedding Catering</h3>
            <p>Catering for wedding celebrations and receptions.</p>
        </article>

        <article class="card">
            <img src="{{ asset('images/image3.jpg') }}" alt="Corporate event">
            <h3>Corporate Events</h3>
            <p>Food services for meetings and organised functions.</p>
        </article>

        <article class="card">
            <img src="{{ asset('images/image 4.avif') }}" alt="Prepared catering food">
            <h3>Parties and Functions</h3>
            <p>Catering for birthdays and other special occasions.</p>
        </article>
    </div>

    <p>
        <a href="{{ route('services') }}" class="button">View All Services</a>
    </p>
</section>

<section class="section">
    <h2>Planning an Event?</h2>
    <p>Send us your catering request so our team can follow it up.</p>
    <a href="{{ route('bookings.create') }}" class="button">Request a Booking</a>
</section>
</x-site-layout>
