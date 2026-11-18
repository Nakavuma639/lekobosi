<x-site-layout title="Booking | Lekobosi">
<section class="section">
    <h1>Book Our Catering Services</h1>
    <p>
        Tell us about your event and catering needs.
        Our team will review your request and communicate
        the decision to you.
    </p>

    {{-- Wired to bookings.store in Phase 2. --}}
    <form class="form-box bookingForm" action="#" method="post">
        @csrf
        <h2>Booking Details</h2>

        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname"
               placeholder="Enter your full name" required>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               placeholder="Enter your email address" required>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone"
               placeholder="Enter your phone number" required>

        <label for="whatsapp">WhatsApp Number</label>
        <input type="tel" id="whatsapp" name="whatsapp"
               placeholder="e.g. 2567XXXXXXXX" required>

        <label for="event">Type of Event</label>
        <select id="event" name="event_type" required>
            <option value="">Select event type</option>
            <option>Wedding</option>
            <option>Introduction Ceremony</option>
            <option>Birthday Party</option>
            <option>Corporate Event</option>
            <option>Conference</option>
            <option>Other</option>
        </select>

        <label for="date">Event Date</label>
        <input type="date" id="date" name="date" required>

        <label for="location">Event Location</label>
        <input type="text" id="location" name="location"
               placeholder="Where will the event take place?" required>

        <label for="guests">Expected Number of Guests</label>
        <input type="number" id="guests" name="guests"
               min="1" placeholder="Enter number of guests" required>

        <label for="details">Additional Requirements</label>
        <textarea id="details" name="details" rows="4"
                  placeholder="Describe your catering needs"></textarea>

        <button type="submit" class="button">
            Submit Booking Request
        </button>

        <p>
            After your request has been reviewed, you will be
            notified of the decision through WhatsApp.
        </p>
    </form>
</section>
</x-site-layout>
