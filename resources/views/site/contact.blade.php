<x-site-layout title="Contact | Lekobosi">
<section class="section">
    <p class="eyebrow">GET IN TOUCH</p>
    <h2>Contact Lekobosi</h2>
    <p>Contact us about catering services and event bookings.</p>

    <div class="cards">
        <article class="card">
            <h3>Location</h3>
            <p>Kyankwanzi, Uganda</p>
        </article>

        <article class="card">
            <h3>Telephone</h3>
            <p>0775847855</p>
        </article>

        <article class="card">
            <h3>Email</h3>
            <p>Maryharriet07@gmail.com</p>
        </article>
    </div>

    {{-- Wired to contact.store in Phase 5. --}}
    <form class="form-box contactForm" action="#" method="post">
        @csrf
        <h3>Send Us a Message</h3>

        <label>Your Name
            <input type="text" name="name" required placeholder="Enter your name">
        </label>

        <label>Email Address
            <input type="email" name="email" required placeholder="Enter your email">
        </label>

        <label>Message
            <textarea rows="5" name="message" required placeholder="Write your message"></textarea>
        </label>

        <button type="submit" class="button">Send Message</button>
    </form>
</section>
</x-site-layout>
