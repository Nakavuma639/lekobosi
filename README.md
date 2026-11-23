# Lekobosi Catering Booking System

The booking website and back office for **Lekobosi Restaurant and Outside Catering Services**, Kyankwanzi, Uganda.

- **Customers:** browse the site, request a catering booking, and track it by reference. With an account, they also see their payments and balance.
- **The owner and staff:** review and approve requests, keep a calendar of confirmed dates, record payments and print receipts, read contact messages, and run reports.

Built with Laravel 13 (PHP 8.3+), MySQL/MariaDB, Blade, Tailwind CSS and Alpine.js. The business rules come from [`BACKEND_SPEC.md`](BACKEND_SPEC.md). How the original HTML frontend was integrated, and every place it differs from the spec, is in [`docs/FRONTEND_NOTES.md`](docs/FRONTEND_NOTES.md).

---

## 1. Local setup (Windows with XAMPP)

### What you need

| Tool | Version | Notes |
|---|---|---|
| PHP | **8.3 or newer** | XAMPP ships PHP 8.2, which is **too old**. Use [Laravel Herd](https://herd.laravel.com) (PHP 8.5) or another PHP ≥ 8.3 on your `PATH`. Check with `php -v`. |
| MySQL / MariaDB | MariaDB 10.4+ or MySQL 8 | XAMPP's MariaDB is fine. Start it in the XAMPP Control Panel. |
| Composer | 2.x | |
| Node.js | 20+ | For building the CSS/JS. |

### Steps

```bash
git clone <repository-url> lekobosi
cd lekobosi

composer install
npm ci

cp .env.example .env          # on Windows PowerShell: copy .env.example .env
php artisan key:generate
```

Create an empty database (utf8mb4), for example in phpMyAdmin or with:

```bash
mysql -u root -e "CREATE DATABASE lehobothweb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Set `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in `.env`, then:

```bash
php artisan migrate
npm run build
```

Create the owner's account. Put the details in `.env` first, then run the command:

```dotenv
ADMIN_SEED_NAME="Harriet Namagembe"
ADMIN_SEED_EMAIL=owner@example.com
ADMIN_SEED_PASSWORD=a-long-password
```

```bash
php artisan lekobosi:create-admin
```

Then **delete `ADMIN_SEED_PASSWORD` from `.env`**. Running the command again later promotes or re-activates that email and resets its password.

Start everything (web server, queue worker and Vite):

```bash
php artisan dev
```

Open http://localhost:8000. The owner logs in at `/login` and lands on `/admin`.

> **Emails are queued.** If you start only `php artisan serve`, emails wait in the queue ("Waiting to send" on a booking page). Use `cphp artisan dev`, or run `php artisan queue:work` in a second terminal.

### Demo data (optional, local only)

```bash
php artisan migrate:fresh --seed
```

This fills the database with **fake** bookings, payments, messages and accounts. All demo accounts use the password `password`:

- `owner@demo.lekobosi.test`
- `staff@demo.lekobosi.test`
- `customer@demo.lekobosi.test`

The seeder refuses to run when `APP_ENV=production`.

---

## 2. Configuration (`.env`)

| Variable | Default | What it does |
|---|---|---|
| `APP_NAME` | `Lekobosi Catering` | Shown in emails and page titles. |
| `APP_ENV` | `local` | Set to `production` on the live server. |
| `APP_DEBUG` | `true` | **Must be `false` in production**, or error pages show internal details. |
| `APP_URL` | `http://localhost:8000` | **Must be the real site address.** Every link in emails ("Track your booking", "Review the booking") is built from it. |
| `APP_TIMEZONE` | `Africa/Kampala` | |
| `DB_*` | | MySQL/MariaDB connection. |
| `SESSION_DRIVER` | `database` | Needed for signing disabled staff out instantly. |
| `SESSION_SECURE_COOKIE` | `false` | Set to `true` in production (HTTPS only). |
| `FORCE_HTTPS` | `false` | `true` in production: redirects HTTP to HTTPS, sends HSTS, and builds https links. |
| `TRUSTED_PROXIES` | | If the host sits behind a proxy or load balancer (e.g. Cloudflare), set it to `*` or the proxy IPs, so HTTPS is detected correctly. |
| `QUEUE_CONNECTION` | `database` | Emails are queued here. |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` | | SMTP for outgoing email. `MAIL_MAILER=log` writes emails to `storage/logs` instead of sending them. |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | | The sender customers see. Use the business's own address. |
| `ADMIN_NOTIFY_EMAIL` | | Where new-booking and new-message alerts go (the owner). Left blank, alerts are logged as "Not sent". |
| `ADMIN_WHATSAPP_NUMBER` | | The business WhatsApp number. |
| `ADMIN_SEED_NAME`, `ADMIN_SEED_EMAIL`, `ADMIN_SEED_PASSWORD` | | Read once by `lekobosi:create-admin`. Remove the password afterwards. |
| `MAX_EVENTS_PER_DAY` | `1` | How many approved events one date can take before it counts as full. |
| `MIN_LEAD_DAYS` | `1` | The earliest bookable date is today plus this many days. This applies to staff entry too. |
| `MAX_GUESTS` | `5000` | Largest guest count accepted. |
| `WHATSAPP_ENABLED`, `WHATSAPP_PROVIDER`, `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_ID` | `false` | Reserved for automatic WhatsApp messages. **Not built yet** (see §6). |
| `MYSQL_TEST_*` | | Optional. The database for the MySQL locking tests (default `lehobothweb_db_testing`). |

The settings the owner may want to change (capacity, notice, guests) are the open questions in spec §13. They're all in `.env`, so changing them needs no code change.

---

## 3. Deploying to production

1. **Server:** PHP ≥ 8.3 with the `pdo_mysql`, `mbstring`, `openssl` and `fileinfo` extensions, plus MySQL/MariaDB. Point the web root at the `public/` folder.
2. **Code:**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
   `public/build` is not committed, so the build step is required.
3. **Settings:** copy `.env.example` to `.env`, fill it in (see the checklist below), then run `php artisan key:generate`.
4. **Database and owner account:**
   ```bash
   php artisan migrate --force
   php artisan lekobosi:create-admin
   ```
   Then remove `ADMIN_SEED_PASSWORD` from `.env`.
5. **Cache the configuration:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
6. **Scheduler (required for emails):** add one cron entry. It drains the email queue every minute, so shared hosting doesn't need a permanent worker:
   ```cron
   * * * * * cd /path/to/lekobosi && php artisan schedule:run >> /dev/null 2>&1
   ```
   If the host allows a permanent process, `php artisan queue:work --tries=3` works too.
7. **Database check:** run `SELECT @@log_bin, @@binlog_format;`. If binary logging is on with `binlog_format=STATEMENT`, change it to `ROW` or `MIXED`. Booking approvals run at READ COMMITTED isolation, which MySQL refuses under statement-based binary logging. See `docs/FRONTEND_NOTES.md` §10.

### Go-live checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` = the real https address
- [ ] `FORCE_HTTPS=true` and `SESSION_SECURE_COOKIE=true` (once the certificate is installed)
- [ ] `TRUSTED_PROXIES` set if behind a proxy
- [ ] `ADMIN_NOTIFY_EMAIL` = **the owner's** address (not a developer's)
- [ ] `MAIL_*` = the **business's** mail account, `MAIL_FROM_ADDRESS` = the business address
- [ ] Any app passwords used during development revoked and replaced
- [ ] Cron entry for `schedule:run` installed. Test it: submit a booking and see the email go out
- [ ] Owner account created; `ADMIN_SEED_PASSWORD` removed from `.env`
- [ ] Demo data **not** seeded
- [ ] Event photos replaced with the client's own (see `docs/FRONTEND_NOTES.md` §1)
- [ ] Owner has confirmed the website copy (services, inclusions, values)
- [ ] `binlog_format` checked (step 7)

---

## 4. Everyday commands

| Command | Purpose |
|---|---|
| `composer run dev` | Local server + queue worker + Vite |
| `php artisan test` | Full test suite |
| `php artisan test tests/Integration` | MySQL row-locking tests (need MySQL running; skipped otherwise) |
| `php artisan queue:work` | Send queued emails |
| `php artisan lekobosi:create-admin` | Create or reset the owner account from `ADMIN_SEED_*` |
| `php artisan migrate:fresh --seed` | **Local only:** wipe and reload demo data |
| `vendor/bin/pint` | Format PHP code |

---

## 5. How it works

### Roles

| Role | Can |
|---|---|
| Guest | Browse, request a booking, track by reference, send a message, register |
| Customer | Same, plus see their own bookings, payments and balances (`/account`) |
| Staff | View all bookings, enter phone/walk-in bookings, edit pending bookings, set quotes, record payments, print receipts, read messages, view reports |
| Owner (admin) | Everything staff can, plus approve/reject/cancel bookings, void payments, mark messages read, download CSV reports, see turned-away requests, manage staff accounts |

### Business rules

- **Approval:**
  - Bookings start **Pending**; only the owner can approve or reject them.
  - A rejection needs a reason, which the customer sees.
  - An approved booking can be cancelled; a rejected one is final.
- **Full dates:**
  - Only **approved** bookings fill a date (`MAX_EVENTS_PER_DAY`).
  - Requests for a full date are refused and saved as "turned-away requests".
  - Approval locks the date in the database, so two approvals can't both take the last slot.
- **References:** customers track bookings with a reference such as `LKB-7K3QX9MD`. References are random (not guessable) and avoid look-alike characters. The tracking page shows status only, never personal details.
- **Payments:**
  - Payments are **recorded by staff**; there is no online payment.
  - The balance is always calculated from confirmed payments, never stored, and overpaying is refused.
  - Mistakes are voided with a reason by the owner, never deleted.
  - Receipts are numbered `RCT-YYYY-NNNNN`; the count restarts each year.
- **Cancelled bookings keep their payments.** The owner settles any refund outside the system, and these amounts are shown separately on the Payments page.
- **Notifications:**
  - Emails go to the owner (new booking, new message) and the customer (booking received, approved, rejected/cancelled, payment received).
  - They're queued, sent after the data is saved, and logged on each booking. A failing mail server never blocks a booking.
- **Staff accounts:**
  - The owner adds staff, who are emailed a link to set their own password.
  - Disabling a staff account signs that person out everywhere immediately.
  - Staff and owner accounts can't be deleted, only disabled, so the history stays intact.

### Code layout

| Where | What |
|---|---|
| `app/Actions/` | Business operations: submit/approve booking, record/void payment, update quote, create staff… |
| `app/Services/` | `BookingAvailability` (full dates), `BookingStatistics`, `NotificationDispatcher`, `ReportBuilder` |
| `app/Http/Controllers/` | Thin controllers: public site, `Admin/` back office, `Customer/` account |
| `app/Http/Requests/` | Validation (field names follow the client's original frontend) |
| `app/Policies/` | Who may do what, alongside the `role:` route middleware |
| `app/Notifications/` | Email notifications |
| `lang/en/whatsapp.php` | Pre-written WhatsApp messages (edit freely) |
| `resources/views/site/` | Public pages · `admin/` back office · `customer/` account |
| `tests/` | Feature, unit and MySQL integration tests |

---

## 6. Open questions and known limits

| # | Question / limit | Current behaviour |
|---|---|---|
| 1 | Can Lekobosi cater more than one event per day? | `MAX_EVENTS_PER_DAY=1` |
| 2 | Same-day walk-in bookings? | Not allowed. Staff entry uses `MIN_LEAD_DAYS` (tomorrow at the earliest). |
| 3 | Should quote changes and edits to booking details record who made them? | Not recorded. Status changes, payments and voids are. |
| 4 | Automatic WhatsApp messages | Not built. Needs a WhatsApp Business API account and approved templates. The "Send on WhatsApp" button opens a pre-written chat for staff to send. |
| 5 | Should receipt numbers restart each year? | Yes (`RCT-2026-…`, `RCT-2027-…`) |
| 6 | Should customers verify their email? | No |
| 7 | CAPTCHA on public forms | No. Rate limiting plus a hidden spam-trap field instead. |

---

## 7. Security checklist (spec §9)

| Requirement | How it's met | Proven by |
|---|---|---|
| Passwords hashed | Laravel `hashed` cast (bcrypt) | `SecurityTest`, `RegistrationTest` |
| All validation on the server | Form Requests on every write | Validation tests in each feature test file |
| Role-based access | `role:` middleware **and** Policies | `SecurityTest` walks **every** `/admin` route as a customer (403) and as a guest (login redirect) |
| Customers can't see others' bookings | `BookingPolicy::view` returns 404 | `CustomerBookingTest`, `RoleAccessTest` |
| No raw SQL with user input | Eloquent / query builder bindings only; the few `whereRaw` fragments are constants with bound values | Code review |
| Mass-assignment protection | Bookings, payments and messages have no fillable fields; `role` and `is_active` aren't fillable on users | `BookingSubmissionTest`, `RegistrationTest` (posted `role`/`status` ignored) |
| CSRF | `PreventRequestForgery` in the web group | `SecurityTest` |
| Sessions | Regenerated on login and invalidated on logout (Breeze); HttpOnly, SameSite=Lax; Secure via `SESSION_SECURE_COOKIE` | `AuthenticationTest` |
| Rate limiting | Login (5/min per email+IP), register, booking, tracking (10/min), contact, availability, password links; each has its own limit | Throttle tests in each feature file |
| No stored HTML | Tags stripped from free text | `BookingSubmissionTest`, `ContactMessageTest` |
| CORS | **Not applicable**: the frontend and backend are the same site (Blade), so no cross-origin requests | — |
| No secrets in code or responses | `.env` is git-ignored; `APP_DEBUG=false` hides error details | `SecurityTest` |
| Minimal tracking data | Only reference, status, event, date, last update; reason only when rejected; identical 404 message | `TrackBookingTest` |
| HTTPS and HSTS | `FORCE_HTTPS=true` redirects and sends HSTS | `SecurityTest` |
| Personal data kept out of logs | Notification and mail failures log ids and exception class only | `NotificationsTest` |
| Attributable admin actions | `decided_by`, `recorded_by`, `voided_by`, `read_by`, status history, `created_by` | Decision, payment and inbox tests |
| Audit trail can't be erased | Status history is append-only; payments are never deleted; staff and owner accounts can't be deleted | `BookingStatusHistoryTest`, `PaymentsTest`, `ProfileTest` |

---

## 8. Testing

```bash
php artisan test                       # everything (uses in-memory SQLite)
php artisan test tests/Integration     # MySQL row-locking tests
```

- **Integration tests:** these need MySQL running and a database named `lehobothweb_db_testing` (or set `MYSQL_TEST_DATABASE`). They **skip silently** when MySQL isn't reachable, so a green run without MySQL hasn't tested the locking.
- **Acceptance testing:** the manual script for the owner, staff and customers is in [`docs/UAT.md`](docs/UAT.md).
