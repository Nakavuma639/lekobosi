<x-site-layout title="Track Booking |Lekobosi">
<main class="section">
    <h1>Track Your Booking</h1>
    <p>
        Enter your booking reference to check the status of your
        catering request.
    </p>

    <!-- BOOKING REFERENCE FORM -->
    <form class="form-box" action="{{ route('bookings.track') }}" method="get">
        <label for="reference">Booking Reference Number</label>
        <input
            type="text"
            id="reference"
            name="reference"
            value="{{ request('reference') }}"
            placeholder="Enter your booking reference"
            required
        >
        <button type="submit" class="button" style="margin-top: 6px;">Check Booking Status</button>
    </form>

    <!-- STATUS INFORMATION -->
    <section class="form-box">
        <h2>Understanding Your Booking Status</h2>

        <div class="notification-box">
            <h3>Pending Review</h3>
            <p>
                Your booking request is waiting for the restaurant
                to review it.
            </p>
        </div>

        <div class="notification-box">
            <h3>Approved</h3>
            <p>
                Your booking has been accepted. Contact the restaurant
                if you need to confirm further arrangements.
            </p>
        </div>

        <div class="notification-box">
            <h3>Rejected</h3>
            <p>
                Your booking request was not accepted. Please contact
                the restaurant for further information.
            </p>
        </div>
    </section>

    <!-- WHATSAPP INFORMATION -->
    <section class="form-box">
        <h2>Booking Updates on WhatsApp</h2>
        <p>
            We will notify you on WhatsApp after the restaurant
            reviews your booking request, once notifications are
            connected to the system.
        </p>
        <a
            href="https://wa.me/256775847855"
            class="button"
            target="_blank"
            rel="noopener"
        >
            Contact Us on WhatsApp
        </a>
    </section>
</main>
</x-site-layout>
