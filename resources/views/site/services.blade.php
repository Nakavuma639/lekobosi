<x-site-layout title="Our Services | Lekobosi">
<section class="section">
    <p class="eyebrow">WHAT WE OFFER</p>
    <h2>Our Catering Services</h2>
    <p>Explore our services and plan your next event with Lekobosi.</p>

    <div class="cards">
        <article class="card">
            <img src="{{ asset('images/image2.jpg') }}" alt="Wedding catering">
            <h3>Wedding Catering</h3>
            <p>Catering arrangements for weddings and receptions.</p>
        </article>

        <article class="card">
            <img src="{{ asset('images/image3.jpg') }}" alt="Corporate catering">
            <h3>Corporate Events</h3>
            <p>Food services for meetings, workshops and functions.</p>
        </article>

        <article class="card">
            <img src="{{ asset('images/image 4.avif') }}" alt="Food for a celebration">
            <h3>Parties and Functions</h3>
            <p>Catering for birthdays, family gatherings and celebrations.</p>
        </article>

        <article class="card">
            <img src="{{ asset('images/lekobosi/tent1.jpg') }}" alt="Outside catering">
            <h3>Outside catering</h3>
            <p>Catering arrangements for Outside catering.</p>
        </article>

        <article class="card">
            <img src="{{ asset('images/lekobosi/image1.jpeg') }}" alt="Food preparation and serving">
            <h3>Food preparation and serving</h3>
            <p>Preparing and serving meals such as breakfast,lunch and dinner </p>
        </article>

        <article class="card">
            <img src="{{ asset('images/image6.webp') }}" alt="Buffet services">
            <h3>Buffet services</h3>
            <p>Providing a variety of dishes from which customers can select their preferred meals</p>
        </article>

        <article class="card">
            <img src="{{ asset('images/snak.avif') }}" alt="snack and pastry services">
            <h3>snack and pastry services</h3>
            <p>Preparing and selling cakes,mandazi,chapati,doughnuts and other snacks</p>
        </article>

        <article class="card">
            <img src="{{ asset('images/out.avif') }}" alt="Special dietary catering">
            <h3>Special dietary catering</h3>
            <p>Preparing meals according to customer's dietary needs and preferences.</p>
        </article>
    </div>

    <p>
        <a href="{{ route('bookings.create') }}" class="button">Request a Booking</a>
    </p>
</section>
</x-site-layout>
