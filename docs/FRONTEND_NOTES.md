# Frontend Notes

Findings from Step 0 (spec §10) and every frontend ↔ spec mismatch. Rule: **frontend wins for naming, the spec wins for business rules.**

## 1. What the frontend was, and what it is now

- **Delivered:** 9 static HTML pages + `style.css` + `script.js` + `images/` in `D:\Lehobothweb\Lehobothweb` (left untouched). Not React as the spec assumed. No framework, no API calls.
- **Integration mode: Blade inside Laravel** (approved 2026-11-18). Same origin → session auth + CSRF, no Sanctum/CORS. Business logic lives in Action classes so a JSON API can be added later.
- **Redesign (approved 2026-11-18):** the developer asked for a professional look. The pages keep the client's content, page structure and form field names, but are restyled with **Tailwind (the same build Breeze uses)**. `style.css` and `script.js` were retired: Alpine handles the mobile menu, and validation is server-side with clear inline errors.

### Design tokens
| Token | Value | Source |
|---|---|---|
| `brand-700` | `#8E1B1E` maroon | Logo plate |
| `saffron-500` | `#EE9A2D` | Logo cloche |
| Neutrals | Tailwind `stone` | |
| Display font | Bricolage Grotesque | Bunny Fonts |
| Body font | Figtree | Breeze default |

### Images
- **Client's own photos kept:** logo, team portraits, their buffet tent (`lekobosi-buffet-tent.jpg`).
- **Removed:** `image2.jpg` carried a visible **iStock watermark** (unlicensed), and `tent1.jpg`, `image3.jpg`, `image 4.avif`, `image6.webp`, `snak.avif`, `out.avif` were unverified stock. All removed.
- **Replaced with Unsplash photos** (Unsplash License: free for commercial use, no attribution required), saved in `public/images/`:
  `hero-garden-event`, `wedding`, `corporate`, `buffet`, `parties`, `snacks`, `local-dishes`, `chef-platter`.

## 2. Where things live

| Purpose | File |
|---|---|
| Public layout | `App\View\Components\SiteLayout` → `layouts/site.blade.php`, `layouts/partials/site-header|site-footer|fonts` |
| Public pages | `resources/views/site/*.blade.php` |
| Reusable form field | `components/site/field.blade.php` (label, `old()`, hint, error, `aria-invalid`) |
| Interior page header | `components/site/page-header.blade.php` |
| Auth pages (login, register, forgot/reset password, verify) | Breeze `layouts/guest.blade.php`, redesigned as a split screen |
| Admin/staff dashboard | `admin/dashboard.blade.php` (Breeze `x-app-layout`) |
| Customer dashboard | `customer/dashboard.blade.php` (Breeze `x-app-layout`) |
| Styles | `resources/css/app.css` (`btn-*`, `field-*` component classes), `tailwind.config.js` |

## 3. Routes

| Page | URL | Route name | Access |
|---|---|---|---|
| Home | `/` | `home` | public |
| About | `/about` | `about` | public |
| Services | `/services` | `services` | public |
| Booking | `/booking` (GET form, POST submit, 5/min) | `bookings.create`, `bookings.store` | public |
| Booking received | `/booking/submitted` | `bookings.confirmation` | only straight after submitting (flashed reference) |
| Full dates | `/availability?month=YYYY-MM` | `availability` | public JSON, dates only, 60/min |
| Track booking | `/track-booking` | `bookings.track` | public |
| Contact | `/contact` | `contact.create` | public |
| Login / Register / Forgot password | `/login`, `/register`, `/forgot-password` | Breeze names | guest |
| Dashboard redirect | `/dashboard` | `dashboard` | signed in → sends to the role's dashboard |
| Customer dashboard | `/account` | `customer.dashboard` | `role:customer` |
| Admin/staff dashboard | `/admin` | `admin.dashboard` | `role:admin,staff` |
| Booking list (filters `status`, `source`, `q`, `from`, `to`, `sort`) | `/admin/bookings` | `admin.bookings.index` | `role:admin,staff` + policy |
| Booking detail | `/admin/bookings/{reference}` | `admin.bookings.show` | `role:admin,staff` + policy |
| Approve / reject / cancel | `PATCH /admin/bookings/{reference}/status` | `admin.bookings.status.update` | `role:admin` + policy `decide` |
| Calendar (`month=YYYY-MM`, `pending=0/1`) | `/admin/calendar` | `admin.calendar` | `role:admin,staff` + policy |
| Contact form submit | `POST /contact` | `contact.store` | public, 5/min (`contact` limiter) |
| Messages inbox (`unread=1`) | `/admin/messages` | `admin.messages.index` | `role:admin,staff` + policy |
| Read a message | `/admin/messages/{id}` | `admin.messages.show` | `role:admin,staff` + policy |
| Mark as read | `PATCH /admin/messages/{id}/read` | `admin.messages.read` | `role:admin` + policy |

## 4. Forms — exact field names

### Booking
| Frontend field | Spec field | Notes |
|---|---|---|
| `fullname` | `customer_name` | |
| `email` | `email` | |
| `phone` | `phone` | |
| `whatsapp` | `whatsapp_number` | Optional (spec rule). The original HTML marked it required. |
| `event_type` | `event_type` | **Bug fixed:** was `name="event-date"`. |
| `date` | `event_date` | `min` = today + `MIN_LEAD_DAYS` |
| `location` | `event_location` | |
| `guests` | `guest_count` | `max` = `MAX_GUESTS` |
| `details` | `extra_requirements` | max 2000 |

**Event types** (from the client's dropdown, not the spec list): `Wedding`, `Introduction Ceremony`, `Birthday Party`, `Corporate Event`, `Conference`, `Other`.
The options now carry `value` attributes from `App\Enums\EventType` (`wedding`, `introduction_ceremony`, `birthday_party`, `corporate_event`, `conference`, `other`), and the client's labels are still what customers see.

Also on the form:
- A hidden honeypot field `website` (must stay empty).
- When the date changes, the form calls `GET /availability?month=YYYY-MM` and warns if the date is already full. The server checks again on submit.
- Signed-in customers get their name, email and phone prefilled.

### Track booking
`reference`, sent by GET to `/track-booking`.
- Input is forgiving: case, spaces and a missing `LKB-` prefix are all accepted.
- Shows only the reference, status, event type, event date and last update, plus the reason when rejected.
- Unknown references return 404 with the same message every time.
- Lookups are limited to 10 a minute per IP; just opening the page doesn't count.

### Contact
`name`, `email`, `phone` (optional), `subject` (optional, ≤150), `message` (10–2000). `phone` and `subject` were added to match the spec. The original form had no `name` attribute on the sender field.

### Login
`email`, `password`, `remember`. A "Forgot password?" link has been added (Breeze reset flow).

### Register
`name`, `email`, `phone`, `password`, `password_confirmation`.
- Changed from the client's `confirm_password` to Breeze's `password_confirmation` (developer's decision).
- Phone is stored as E.164 (`+256…`). Accepted input: `07…`, `2567…`, `+2567…`, also `03…`/`04…` landlines, with or without spaces or dashes.
- Role is always `customer`; posted `role`/`is_active` are ignored.

## 5. Placeholder data still to replace

| Where | Placeholder | Phase |
|---|---|---|
| Admin dashboard | total outstanding balance tile | 7 |
| Customer dashboard | quote, paid and balance per booking (the booking list itself is live) | 7 |

## 6. UI gaps (in the spec, no screen yet)

Payments & Balances · printable receipts · reports · staff phone/walk-in booking entry · editing booking details · conflict attempts · staff account management. These are built as Breeze-styled admin pages in their phases. (The booking detail page, approve/reject and the calendar were built in Phase 4.)

## 7. Open questions (spec §13) — defaults in use

Configurable in `.env` / `config/lekobosi.php`: `MAX_EVENTS_PER_DAY=1`, `MIN_LEAD_DAYS=1`, `MAX_GUESTS=5000`. Guests may book without an account. Only `admin` approves/rejects. No email verification. WhatsApp disabled (`wa.me` links only). Approved → Rejected allowed for admin with a reason. No CAPTCHA (throttling + honeypot).

`ADMIN_NOTIFY_EMAIL` points to the developer's address for testing. Switch it to the owner's before handover.

## 8. Notifications (Phase 6)

| Event | Recipient | Channel |
|---|---|---|
| `booking_submitted` | Owner (`ADMIN_NOTIFY_EMAIL`) | email |
| `booking_received` | Customer, with the reference | email |
| `booking_approved` | Customer, with the quote if set | email |
| `booking_rejected` | Customer, with the reason (cancellation wording if it was approved) | email |
| `contact_message` | Owner | email |

- **Delivery:** queued (`database` queue), sent only after the transaction commits, 3 tries.
- **Logging:** every attempt is recorded in `notification_logs` (queued → sent / failed / skipped) and shown on the booking page.
- **Failures:** a failure never affects the booking or decision. Errors stay in `notification_logs`; application logs get no addresses.
- **Running the queue:**
  - Locally, `composer run dev` already runs `queue:listen`. Otherwise run `php artisan queue:work`.
  - In production, one cron entry `* * * * * php artisan schedule:run` drains the queue every minute (`routes/console.php`).
- **WhatsApp:** the booking page has a "Send on WhatsApp" button (wa.me with a pre-written message for the current status). Templates live in `lang/en/whatsapp.php`. Automatic WhatsApp messages are **not** built; they need a WhatsApp Business API account.

## 9. Environment notes

- Laravel 13 needs PHP ≥ 8.3. XAMPP ships PHP 8.2, so artisan/composer/tests run on Herd's PHP 8.5; XAMPP provides **MariaDB 10.4 only**.
- Run `npm run build` (or `npm run dev`) after view changes. Pages load assets through Vite.
- `php artisan db:show` fails on MariaDB 10.4 (`performance_schema` disabled). Cosmetic only.
- Tests use in-memory SQLite, except `tests/Integration`. Those tests prove the approval row lock against a real MySQL database, `lehobothweb_db_testing` (override it with `MYSQL_TEST_*` env vars), and skip themselves if MySQL isn't running.
- Approvals run at READ COMMITTED on MySQL. Same-date approvals still wait for each other's row locks, but neighbouring dates don't block each other (no gap locks).
- **Deployment check:** run `SELECT @@log_bin, @@binlog_format;` on the production database. If binary logging is on with `binlog_format=STATEMENT`, InnoDB refuses writes under READ COMMITTED (error 1665) and every approval would fail. In that case use `ROW`/`MIXED`, or remove `useReadCommittedIsolation()` from `ChangeBookingStatus`. The same-date safety comes from the row locks, not the isolation level.
