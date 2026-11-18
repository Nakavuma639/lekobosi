<x-site-layout title="Admin Dasboard | Lekobosi" chrome="admin">
<main class="admin-content">
    <h1>Welcome, Administrator</h1>
    <p>Manage catering bookings and customer requests.</p>

    <!-- BOOKING NOTIFICATIONS -->
    <section class="admin-panel">
        <h2>Booking Notifications</h2>

        <div class="notification-box">
            <h3>New Booking Requests</h3>
            <p><strong>Reminder:</strong> Review pending requests
                and communicate your decision to customers.</p>
        </div>
    </section>

    {{-- Live totals replace these zeros in Phase 4. --}}
    <!-- DASHBOARD SUMMARY -->
    <section class="stats">
        <div class="stat">
            <h3>Total Bookings</h3>
            <p>0</p>
        </div>

        <div class="stat">
            <h3>Pending Requests</h3>
            <p>0</p>
        </div>

        <div class="stat">
            <h3>Approved Bookings</h3>
            <p>0</p>
        </div>

        <div class="stat">
            <h3>Rejected Requests</h3>
            <p>0</p>
        </div>
    </section>

    <!-- BOOKINGS TABLE -->
    <section class="admin-panel">
        <h2>Manage Customer Bookings</h2>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>WhatsApp Number</th>
                        <th>Event Type</th>
                        <th>Event Date</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">
                            No bookings to display yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- WHATSAPP NOTIFICATIONS -->
    <section class="admin-panel">
        <h2>WhatsApp Notifications</h2>
        <p>
            The system will notify the administrator when a new
            booking is submitted and notify customers after their
            requests are approved or rejected.
        </p>
        <p>
            Automatic messages will be enabled after connecting
            the WhatsApp Business service and backend.
        </p>
    </section>
</main>
</x-site-layout>
