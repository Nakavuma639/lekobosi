# User acceptance test script

**Who:** the owner, one or two staff members, and a few people acting as customers.
**Time needed:** about 45 minutes.
**What you need:**
- The test website address.
- The owner's login.
- Two or three email addresses you can check (to see the customer emails).
- A phone, to try the WhatsApp buttons.

Use made-up customers and events only, never real customers.

Work through each step in order. Tick **Pass** if what you see matches the "You should see" column. If it doesn't, tick **Fail** and write what happened in **Notes**.

---

## Part A: The owner sets up a staff member

| # | Do this | You should see | Pass / Fail | Notes |
|---|---|---|---|---|
| A1 | Log in as the owner at `/login`. | The admin dashboard, "Welcome, (your name)". | | |
| A2 | Click **Staff**, then **Add staff member**. Enter a staff name, an email you can check, and a phone number. Click **Create account**. | "Account created… has been emailed a link to set their password." | | |
| A3 | Open that staff email inbox. | An email with a link to set a password (check spam too). | | |
| A4 | Use the link to set a password, then log in as the staff member in a different browser (or a private window). | The staff member sees the dashboard. The menu has no **Staff** link. | | |

## Part B: A customer books online

| # | Do this | You should see | Pass / Fail | Notes |
|---|---|---|---|---|
| B1 | As a visitor (not logged in), go to the website and click **Book an event**. | The booking form. | | |
| B2 | Click **Send booking request** without filling anything in. | Messages under each required field explaining what's missing. | | |
| B3 | Fill in the form with a test customer, **your test email address**, a date about a month away, 150 guests. Leave WhatsApp blank. Send it. | "We've received your booking request" with a reference like **LKB-XXXXXXXX**. Write the reference down. | | |
| B4 | Check the test customer's inbox. | "We've received your booking request", with the same reference. | | |
| B5 | Check the owner's inbox. | "New booking request: …" with a **Review the booking** button. | | |
| B6 | On the website, open **Track a booking** and enter the reference in lowercase. | Status **Pending**, the event and the date. **No** name, phone or location shown. | | |
| B7 | Track a made-up reference such as `LKB-22222222`. | "We couldn't find a booking with that reference…" | | |

## Part C: Staff take a phone booking

| # | Do this | You should see | Pass / Fail | Notes |
|---|---|---|---|---|
| C1 | As staff, click **New phone booking**. Choose **Phone**, enter a customer with **no email**, a different date, 80 guests. Click **Create booking**. | The booking page, "Booking LKB-… created. Give the customer this reference…" and "entered by (staff name)". | | |
| C2 | On that booking, open **Emails**. | The owner alert is listed. The customer email says **Not sent: No email address for this customer.** | | |
| C3 | Click **Edit details**, change the guest count to 90, and save. | "Booking details updated." The guests now show 90. | | |
| C4 | Try to approve the booking as staff. | There is no approve button: "Only the owner can approve or reject bookings." | | |

## Part D: The owner decides

| # | Do this | You should see | Pass / Fail | Notes |
|---|---|---|---|---|
| D1 | As the owner, open the dashboard. | **Awaiting decision** shows the bookings from B and C. | | |
| D2 | Open the booking from Part B. Enter a quote of **4,500,000** and click **Approve booking**. | "…is now Approved." The quote shows UGX 4,500,000. | | |
| D3 | Check the test customer's inbox. | "Your booking is confirmed", showing the quote. | | |
| D4 | Track the reference on the website again. | Status **Approved**. | | |
| D5 | As a visitor, try to book **the same date** as D2. | "That date is already booked. Please choose another date." | | |
| D6 | As the owner, open **Bookings**, then **Turned-away requests**. | The visitor from D5 is listed, with call and WhatsApp links. | | |
| D7 | Open the phone booking from Part C. Type a reason ("We are away that weekend") and click **Reject booking**. | "…is now Rejected." The history shows the reason. | | |
| D8 | On the D2 booking, click **Send on WhatsApp**. | WhatsApp opens with a ready-written confirmation message to the customer's number. | | |
| D9 | Open **Calendar**. | The approved booking appears on its date in green. | | |

## Part E: Payments and receipts

| # | Do this | You should see | Pass / Fail | Notes |
|---|---|---|---|---|
| E1 | As staff, open the approved booking. Under **Record a payment**, enter **1,500,000**, MTN Mobile Money, a transaction ID, note "Deposit". Click **Record payment**. | "UGX 1,500,000 recorded on receipt RCT-…". Paid UGX 1,500,000; Balance UGX 3,000,000; **Part paid**. | | |
| E2 | Check the customer's inbox. | "Payment received", with the receipt number and a balance of UGX 3,000,000. | | |
| E3 | Click **Receipt** next to the payment, then **Print receipt**. | A tidy receipt with the business name, receipt number, booking reference, amount and balance. It prints on one page. | | |
| E4 | Try to record **5,000,000** (more than the balance). | "That is more than the balance of UGX 3,000,000…" Nothing is recorded. | | |
| E5 | Record the remaining **3,000,000** in cash. | Balance UGX 0; **Paid in full**. | | |
| E6 | As staff, look for a **Void** link on a payment. | There is none. Only the owner can void. | | |
| E7 | As the owner, void the cash payment with the reason "Test". | The payment is struck through and marked **Voided**. Balance is back to UGX 3,000,000. | | |
| E8 | Open **Payments**. | The totals and the booking appear with the correct quote, paid amount and balance. Filter **Outstanding only**: the booking is listed. | | |
| E9 | Check the dashboard. | **Outstanding balance** matches the Payments page. | | |

## Part F: Customer account

| # | Do this | You should see | Pass / Fail | Notes |
|---|---|---|---|---|
| F1 | As a visitor, click **Log in**, then **Create a customer account**, using a test email. | You're taken to **My bookings**, with a welcome message. | | |
| F2 | Click **Request a booking**: your name, email and phone are already filled in. Send it. | The booking appears under **My bookings**. | | |
| F3 | As the owner, approve it with a quote and record a deposit. Then, as the customer, refresh **My bookings** and open the booking. | "Paid X of Y, Balance Z", and the payment listed with its receipt number. | | |
| F4 | Use the **Forgot password?** link on the login page. | A reset email arrives and the new password works. | | |

## Part G: Messages and reports

| # | Do this | You should see | Pass / Fail | Notes |
|---|---|---|---|---|
| G1 | As a visitor, send a message on the **Contact** page. | "Thank you. Your message has been sent…" | | |
| G2 | Check the owner's inbox. | "New message from…" | | |
| G3 | As the owner, open **Messages**. Open the message and click **Mark as read**. | The **Unread messages** count on the dashboard goes down by one. | | |
| G4 | Open **Reports**, then **Bookings**, for this month and next. | Counts by status, event type and source. The test bookings are listed. | | |
| G5 | Open **Reports**, then **Payments**, and click **Download CSV**. | A file that opens in Excel with the test payments. | | |
| G6 | As staff, open **Reports**. | The reports are visible, but there is **no** Download CSV button. | | |

## Part H: Disabling staff

| # | Do this | You should see | Pass / Fail | Notes |
|---|---|---|---|---|
| H1 | With the staff member still logged in (Part A), the owner clicks **Disable** next to them on the **Staff** page. | "…account is disabled and they have been signed out." | | |
| H2 | In the staff member's browser, click any page. | They're sent to the login page: "Your account has been disabled. Please speak to the owner." | | |
| H3 | The staff member tries to log in again. | "These credentials do not match our records." | | |
| H4 | The owner clicks **Enable**. The staff member logs in again. | It works. | | |

---

**Signed off by:**

| Role | Name | Date | Signature |
|---|---|---|---|
| Owner | | | |
| Staff | | | |
| Developer | | | |
