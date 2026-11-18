Project overview
Lekobosi Restaurant and Outside Catering Services needs a website that allows customers to learn about the business, submit event booking requests, register and log in, and track bookings. The administrator should be able to review booking requests, update their status and manage booking/payment information. 
 Customer registration
Customer enters name, email, phone number and password on the Register page.
Backend validates the details and checks that the email is not already registered.
Password is securely hashed before saving; never store a plain-text password.
After successful registration, show confirmation and allow the customer to log in.
 Customer login and logout
Customer enters email and password.
Backend verifies the credentials and starts a secure session if they are correct.
Show a clear error for incorrect details. Logout must end the session.
Restrict customer only and administrator only pages to the correct logged in role.
 Submit a booking
Connect the existing Booking form to the backend.
The current form collects customer name, email, telephone number, WhatsApp number, event type, event date, event location, number of guests and extra requirements.
Validate required fields, contact details, guest count and event date on the server.
Save each valid booking in the database with a unique booking reference and initial status Pending.
Show the customer a confirmation and their booking reference.
 Administrator manages bookings
Administrator logs in through an authorised admin account.
Dashboard displays live totals: total bookings, pending requests and approved/confirmed bookings.
Administrator can view booking details and approve or reject a request.
Save every status change and its date/time. The customer must see the latest status when tracking.
Only authorised administrators can access these functions.
Track a booking
Customer enters the booking reference on Track Booking page.
Backend searches for the reference and displays the matching booking status: Pending, Approved or Rejected.
If no matching reference exists, show a helpful error without revealing another customer's information.
Here we wanted the system to reject double booking though  if its possible store them somewhere or we make it general and we just reject ,how do u see it?
 Contact messages
Connect the Contact form to the backend.
Validate and save the sender's details and message so the business can review it.
Show a confirmation after successful submission.
Notifications
Notify the administrator when a new booking is submitted.
Notify the customer when the administrator approves or rejects a booking.
The intended channel is WhatsApp, but automatic messages require an agreed WhatsApp Business/API provider, account setup and message templates. (I really don’t know how this will happen coz we really need it)
If WhatsApp integration is not available at first, booking storage and status updates must still work.
Payments and balances 
The administrator page contains a Payments and Balances area.
Id it possible for the customers to pay online or we make it manually like (I really dnt know how we can integarate it coz we also want to track payments of cuatomers)

Suggested database records
Users: name, email, phone, hashed password, role (customer/admin), created date.
Bookings: unique reference, customer link, contact details, event type, event date, location, guest count, extra requirements, status and timestamps.
Contact messages: sender name, email, message/subject and date received.
Payments (if agreed): booking link, quoted amount, amount paid, balance, payment method/reference and payment status.
Booking status history (recommended): booking, previous status, new status, administrator and date/time.
 Security requirements
Hash passwords; do not store plain-text passwords.
Validate information on the server, not only in the browser.
Use role-based access so customers cannot use administrator functions.
Use safe database queries and protect against common web attacks.
Do not expose database credentials or private customer information.
Use HTTPS when deployed online and keep secrets out of public source files.