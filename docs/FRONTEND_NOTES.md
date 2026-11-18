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
| Booking | `/booking` | `bookings.create` | public |
| Track booking | `/track-booking` | `bookings.track` | public |
| Contact | `/contact` | `contact.create` | public |
| Login / Register / Forgot password | `/login`, `/register`, `/forgot-password` | Breeze names | guest |
| Dashboard redirect | `/dashboard` | `dashboard` | signed in → sends to the role's dashboard |
| Customer dashboard | `/account` | `customer.dashboard` | `role:customer` |
| Admin/staff dashboard | `/admin` | `admin.dashboard` | `role:admin,staff` |

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
| Booking form | `action="#"`, no reference shown | 2 |
| Track page | no result area | 3 |
| Admin dashboard | booking totals and booking table (empty state) | 4 |
| Contact form | `action="#"` | 5 |
| Customer dashboard | "My bookings" (empty state) | 7 |

## 6. UI gaps (in the spec, no screen yet)

Payments & Balances · booking detail with approve/reject · confirmed-dates calendar · printable receipts · reports · staff phone/walk-in booking entry · conflict attempts · contact messages inbox · staff account management. These are built as Breeze-styled admin pages in their phases.

## 7. Open questions (spec §13) — defaults in use

Configurable in `.env` / `config/lekobosi.php`: `MAX_EVENTS_PER_DAY=1`, `MIN_LEAD_DAYS=1`, `MAX_GUESTS=5000`. Guests may book without an account. Only `admin` approves/rejects. No email verification. WhatsApp disabled (`wa.me` links only). Approved → Rejected allowed for admin with a reason. No CAPTCHA (throttling + honeypot).

`ADMIN_NOTIFY_EMAIL` points to the developer's address for testing. Switch it to the owner's before handover.

## 8. Environment notes

- Laravel 13 needs PHP ≥ 8.3. XAMPP ships PHP 8.2, so artisan/composer/tests run on Herd's PHP 8.5; XAMPP provides **MariaDB 10.4 only**.
- Run `npm run build` (or `npm run dev`) after view changes. Pages load assets through Vite.
- `php artisan db:show` fails on MariaDB 10.4 (`performance_schema` disabled). Cosmetic only.
- Tests use in-memory SQLite. The approve-under-lock test (Phase 4) needs a MySQL test database.
