# Frontend Notes

Findings from Step 0 (spec §10) and every frontend ↔ spec mismatch. Rule: **frontend wins for naming, the spec wins for business rules.**

## 1. What the frontend is

- **Not React.** The spec assumes a React SPA. The delivered frontend (`D:\Lehobothweb\Lehobothweb`) is 9 static HTML pages + `style.css` + `script.js` + `images/`. No framework, no bundler, no API calls, no mock data layer.
- **Integration mode chosen: merged into Laravel as Blade views** (approved by the developer, 2026-11-18). Same origin → session auth + CSRF, no Sanctum/CORS needed. Business logic still goes in Action classes so a JSON API can be added later.
- The original folder is left untouched; the ported copy lives in this repo.

## 2. Where things live now

| Original | Laravel |
|---|---|
| `style.css` | `public/css/style.css` (one `url()` path changed to `../images/…`; two rules appended: `.form-error`, `.form-success`) |
| `script.js` | `public/js/script.js` (booking date selector fixed, see §4) |
| `images/` | `public/images/` |
| Shared `<head>`, header, footer | `App\View\Components\SiteLayout` → `layouts/site.blade.php` + `layouts/partials/*` (`chrome` = `public` / `auth` / `admin`) |
| `index.html` … `contact.html` | `resources/views/site/*.blade.php` |
| `login.html`, `register.html` | `site/login.blade.php`, `site/register.blade.php` (rendered by Breeze's controllers) |
| `admin.html` | `resources/views/admin/dashboard.blade.php` (route added in Phase 1 behind the role middleware) |

## 3. Routes

| Page | URL | Route name |
|---|---|---|
| Home | `/` | `home` |
| About | `/about` | `about` |
| Services | `/services` | `services` |
| Booking | `/booking` | `bookings.create` |
| Track booking | `/track-booking` | `bookings.track` |
| Contact | `/contact` | `contact.create` |
| Login / Register | `/login`, `/register` | `login`, `register` (Breeze) |

## 4. Forms — exact field names (after wiring fixes)

### Booking (`site/booking`)
| Frontend field | Type | Spec field | Notes |
|---|---|---|---|
| `fullname` | text, required | `customer_name` | |
| `email` | email, required | `email` | |
| `phone` | tel, required | `phone` | |
| `whatsapp` | tel, **required** | `whatsapp_number` | Spec says optional. Server keeps it **optional** (business rule); browser still requires it. |
| `event_type` | select, required | `event_type` | **Bug fixed:** was `name="event-date"`. |
| `date` | date, required | `event_date` | |
| `location` | text, required | `event_location` | |
| `guests` | number, min 1, required | `guest_count` | |
| `details` | textarea | `extra_requirements` | |

**Event types** (options have no `value`, so the label is submitted): `Wedding`, `Introduction Ceremony`, `Birthday Party`, `Corporate Event`, `Conference`, `Other`. The `EventType` enum is built from these, **not** the spec's suggested list (spec has `meeting`/`graduation`; frontend has `Corporate Event`/`Conference`).

### Track booking
`reference` (text, required), submitted by GET to `bookings.track`.

### Contact
`name` (text, required), `email` (required), `message` (required). **Bug fixed:** the name field had no `name` attribute. No `phone`/`subject` fields; spec lists them as optional, so they stay optional/nullable.

### Login
`email`, `password`. **Bug fixed:** neither field had a `name`.

### Register
`name`, `email`, `phone`, `password`, `confirm_password`.
- **Bug fixed:** phone had no `name`; email/password were not `required`.
- Uses `confirm_password`, not Laravel's `password_confirmation` → validate with `same:password` (Phase 1).

## 5. Frontend bugs fixed during the port

- Duplicate `class` attributes (`class="form-box" class="bookingForm"`) meant `script.js` never found the booking, contact, login or register forms → merged into one `class`.
- `lognin-form` typo → `login-form`; register form now carries the `registerForm` class the script looks for.
- `script.js` booking validation read `[name="event_date"]`, which doesn't exist → `[name="date"]`.
- `about.html` left `<section class="section">` and `<section class="stats">` unclosed → closed.
- Navigation and footer links differed page to page (some had Track Bookings, some a duplicate Login link). Unified into one header and one footer containing all links.
- Admin "Logout" was a link to `login.html` (wouldn't end a session) → POST form to `logout`. Public header shows Logout when signed in.

## 6. Placeholder data to replace with backend data

| Where | Placeholder | Phase |
|---|---|---|
| Booking form | `action="#"` | 2 |
| Booking success | no place to show the reference | 2 |
| Track page | no result area | 3 |
| Admin stats | four hard-coded `0`s | 4 |
| Admin bookings table | "No bookings to display yet." row | 4 |
| Contact form | `action="#"`, no confirmation area | 5 |

## 7. UI gaps (in the spec, no screen exists — reported, not built)

- **Payments & Balances** area (the requirements say it's on the admin page; `admin.html` has none)
- Customer "my bookings" / balance view
- Booking detail / approve-reject screen (the admin table has an Actions column, nothing more)
- Confirmed-dates calendar
- Printable receipts
- Booking and payment reports
- Staff phone/walk-in booking entry
- Conflict attempts list
- Contact messages inbox
- Staff account management

## 8. Open questions (spec §13) — defaults in use

Configurable in `.env` / `config/lekobosi.php`:
`MAX_EVENTS_PER_DAY=1`, `MIN_LEAD_DAYS=1`, `MAX_GUESTS=5000`. Guests may book without an account. Only `admin` approves/rejects. No email verification. WhatsApp disabled (`wa.me` links only). Approved → Rejected cancellation allowed for admin with a reason. No CAPTCHA (throttling + honeypot).

`ADMIN_NOTIFY_EMAIL` is set to the business email shown in the footer; confirm with the owner.

## 9. Environment notes

- Laravel 13 needs PHP ≥ 8.3. XAMPP ships PHP 8.2, so artisan/composer/tests run on Herd's PHP 8.5; XAMPP is used **only for MariaDB 10.4**.
- `php artisan db:show` fails on MariaDB 10.4 (`performance_schema` disabled). Cosmetic; the app is unaffected.
- Tests run on in-memory SQLite (`phpunit.xml`). The approve-under-lock test (Phase 4) needs a MySQL test database to be meaningful.
