# Lekobosi Catering Booking System — Backend Specification

**Version:** 1.0 · **Date:** 2026-11-18
**Built from:** (1) *Backend documentation* (the students' requirement notes) and (2) *Group 49 updated project proposal* (MUBS, October 2026).
**Source tags used below:** `[BD]` = Backend documentation · `[P]` = Proposal · `[NEW]` = decision or detail added in this spec to close a gap.

---

## 0. How to use this document (read first)

This file is the single source of truth for the backend. It is written for an AI coding agent (Claude Code) and for the developer supervising it.

**Rules of engagement**

1. **The frontend already exists.** Do not redesign it. Your first job is to *discover* it (Section 10), then build a backend that fits it. Where the frontend and this spec disagree on a field name, route or option list, the **frontend wins** for naming; this spec wins for business rules. Record every such mismatch in `docs/FRONTEND_NOTES.md`.
2. **Do not guess business rules.** Anything not covered here, or listed in Section 13 (Open Questions), uses the stated default. If there is no default, stop and ask.
3. **Build in the phases in Section 11, in order.** Finish each phase's acceptance criteria and tests before starting the next.
4. **Keep controllers thin.** Put business logic in Action/Service classes so it works whether the frontend talks to JSON routes or Inertia pages.
5. **Never invent features** listed under "Out of scope" (Section 3).
6. **No real customer data in tests or seeders.** Use fake sample data only. `[P]`
7. **Never commit secrets.** `.env` stays out of git; maintain `.env.example`.

---

## 1. Project summary

**Client:** Lekobosi Restaurant and Outside Catering Services, Kyankwanzi, Uganda. Serves walk-in restaurant customers and outside events (weddings, introductions, birthdays, meetings, graduations, etc.).

**Problem today:** Bookings arrive by phone, WhatsApp and walk-ins, and are written in notebooks or kept on phones. The owner cannot easily tell which dates are taken, what each customer has paid, or what is still owed. `[P]`

**What the system does:** A website where customers learn about the business, register/log in, submit event booking requests and track them; and where the owner/staff review requests, approve or reject them, manage confirmed dates, record payments and balances, and see basic reports. Notifications go to the admin on new bookings and to the customer on decisions.

**Key principle `[P]`:** the system must **not** auto-confirm a booking. The owner's approve/reject decision is a required step.

### 1.1 Actors and roles

| Role (`users.role`) | Who | Can do |
|---|---|---|
| *(guest)* | Anyone, not logged in | Browse, submit a booking, track by reference, send a contact message, register, log in |
| `customer` | Registered customer | Everything a guest can, plus view own bookings and own payment balances |
| `staff` | Authorised Lekobosi staff `[P]` | Enter phone/walk-in bookings, view bookings, record payments, view balances. **Cannot** approve/reject unless owner decides otherwise (see 13) |
| `admin` | The owner `[BD][P]` | Everything: approve/reject, quotes, payments, reports, manage staff accounts, contact messages |

> `[NEW]` The Backend doc lists only `customer/admin`; the proposal also names **staff**. Implement all three roles; if the owner does not want staff accounts, simply never create any.

---

## 2. Decisions on the students' open questions

The Backend doc contains three unanswered questions. These are the resolved decisions the agent must implement.

### 2.1 Double booking — "reject, store somewhere, or keep it general?"

**Decision: reject at the door, but keep a record of the attempt.**

- **What blocks a date:** only **Approved** bookings. A *Pending* request does **not** block a date, because the owner has not decided yet. Multiple pending requests for the same date are allowed.
- **Capacity:** `MAX_EVENTS_PER_DAY` (env/config, **default `1`**). A date is "full" when approved bookings on that date ≥ capacity. The owner may have teams for more than one event a day — confirm (Section 13).
- **On submission:** if the requested date is full → **reject with HTTP 422**, a friendly message ("That date is already booked. Please choose another date"), and **do not create a booking**.
- **Keep the attempt:** write a row to `booking_conflict_attempts` (name, contact, requested date, event type, time). Purpose: the owner can see lost demand and call the customer back. `[NEW]`
- **On approval:** the same check runs again **inside a DB transaction with a row lock**, so two admins (or two clicks) can't approve two bookings for a full date. If it fails → 409 with a clear message.
- **Admin UI hint:** when a booking is approved, other *Pending* bookings on that date are returned with `has_date_conflict: true` so the dashboard can flag them. They are **not** auto-rejected; the admin decides.
- **Public availability:** `GET /api/availability` returns only the list of **full dates** so the booking form can disable them. It exposes no customer data.

### 2.2 Online payments vs manual recording

**Decision: manual recording only (Phase 1). Online/mobile-money payment is out of scope.**

- The **approved proposal explicitly excludes direct mobile-money integration** (needs provider agreements, cost and time). `[P]` Customers already pay in cash or mobile money; staff record each payment with method and reference. `[P]`
- The system **tracks** payments fully: quoted price, each payment, running balance, status, receipts, outstanding-balances report. This answers the students' need to "track payments of customers". `[BD][P]`
- **Design for the future:** each payment row has `method`, `reference` and `status`, so a gateway (e.g. a Uganda mobile-money/card aggregator) can later create payment rows automatically without schema changes. Do **not** build that now.

### 2.3 WhatsApp notifications

**Decision: build a channel abstraction; ship Email + in-app now; add WhatsApp as a pluggable channel when the owner has an account.**

Automatic WhatsApp messages need a WhatsApp Business API route (Meta's Cloud API directly, or a Business Solution Provider), a verified business account, a dedicated number, and **pre-approved message templates** for business-initiated messages. Template messages are typically billed per message — check the current provider rates before committing. None of this is code the agent can finish alone. `[BD]`

So (details in Section 8):

| Phase | Admin on new booking | Customer on approve/reject |
|---|---|---|
| **Now** | Email to admin + unread badge on dashboard | Email to customer + status visible on Track page |
| **Now (zero-cost WhatsApp)** | — | In the admin booking view, a **"Send via WhatsApp" button** that opens a `wa.me` link with the message pre-filled (staff taps send). No API needed. |
| **Later** | WhatsApp template message | WhatsApp template message |

Booking storage and status changes **must work even if every notification fails**. `[BD]`

---

## 3. Scope

### In scope — Core (from Backend doc) `[BD]`
Customer registration, login/logout, role-based access, booking submission with unique reference and status Pending, admin dashboard (live totals), approve/reject with timestamped history, booking tracking by reference, contact form messages, notifications, payments & balances area.

### In scope — From the proposal `[P]` (build after Core)
- Staff/owner **enter bookings received by phone or walk-in** (customer may have no account).
- **Confirmed-dates calendar** for the admin.
- **Payment recording** with method + reference, **balance calculation**, **printable receipts**.
- **Customers see their payment balance.**
- **Notification when a payment is recorded.**
- **Basic booking and payment reports.**

### Out of scope — do not build `[P]`
Direct mobile-money/card integration · stock control · staff payroll · mobile app · online table reservations for the restaurant · multi-business/multi-tenant support.

---

## 4. Tech stack and conventions

| Item | Choice |
|---|---|
| Language/framework | **PHP + Laravel** (latest stable; check `composer.json` if a project already exists) `[P]` |
| Database | **MySQL** (MariaDB compatible) `[P]` |
| Frontend | React (existing, built by the students) — see Section 10 |
| Auth | Laravel session auth (Sanctum SPA cookie auth if the frontend is a separate SPA; built-in starter-kit auth if merged via Inertia). **No** tokens in localStorage. |
| Queue | Laravel queue. Use `database` driver; if hosting has no worker, run `queue:work --stop-when-empty` from the cron scheduler. Notifications must be queued so a slow/failed send never blocks a request. |
| Mail | SMTP via `.env`; `log` driver in local/dev |
| Tests | Pest or PHPUnit (follow what the repo already uses) |
| Version control | Git; conventional small commits per task `[P]` |

**Conventions**
- Timezone: `APP_TIMEZONE=Africa/Kampala`. `event_date` is a **date only** (no time).
- Money: **UGX, stored as unsigned integers** (no decimals, no floats).
- Phone numbers: normalise to **E.164** (`+2567XXXXXXXX`) on save; accept `07XXXXXXXX`, `2567XXXXXXXX`, `+2567XXXXXXXX` as input.
- IDs: auto-increment integers internally; the **public identifier is the booking `reference`**, never the numeric id.
- API responses: JSON via Laravel API Resources; validation errors use Laravel's standard 422 shape (`message`, `errors{field:[…]}`). Use 401 (not logged in), 403 (wrong role), 404, 409 (conflict), 429 (rate limited).
- Use Form Requests for validation, Policies/middleware for authorization, Eloquent/query builder only (no string-built SQL).
- Naming: tables plural snake_case; enums as PHP backed enums.

---

## 5. Data model

> `[NEW]` The Backend doc suggests one "Payments" record holding quoted amount, amount paid and balance. That is fragile (one payment can't represent instalments). This spec splits it: **`bookings.quoted_amount`** plus a **`payments` ledger**; amount paid and balance are *calculated*, never stored.

### 5.1 `users` `[BD]`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string(100) | |
| email | string(190) | **unique**, lowercase on save |
| phone | string(20) | E.164 |
| password | string | hashed (bcrypt/argon2id via Laravel `Hash`) |
| role | enum(`customer`,`staff`,`admin`) | default `customer`; **never settable from public input** |
| email_verified_at | timestamp null | optional (see 13) |
| is_active | bool | default true; lets admin disable a staff account |
| remember_token, created_at, updated_at | | |

### 5.2 `bookings` `[BD]`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| reference | string(16) | **unique**, see 6.2 |
| user_id | FK users null | null for guest/phone/walk-in bookings |
| created_by | FK users null | staff/admin who entered it, if not self-service |
| source | enum(`online`,`phone`,`walk_in`) | default `online` `[P]` |
| customer_name | string(100) | |
| customer_email | string(190) null | required for `online`; optional for phone/walk-in |
| customer_phone | string(20) | |
| whatsapp_number | string(20) null | |
| event_type | enum | values must match the frontend dropdown; suggested: `wedding`,`introduction`,`birthday`,`meeting`,`graduation`,`other` |
| event_date | date | indexed |
| event_location | string(255) | |
| guest_count | unsigned int | |
| extra_requirements | text null | max 2000 chars |
| status | enum(`pending`,`approved`,`rejected`) | default `pending` |
| status_changed_at | timestamp null | |
| decided_by | FK users null | |
| decision_note | string(500) null | required when rejecting |
| quoted_amount | unsigned bigint null | UGX; set by admin/staff, usually at approval `[P]` |
| created_at, updated_at | | |

Indexes: `reference` (unique), `(event_date, status)`, `status`, `user_id`, `customer_phone`.

### 5.3 `booking_status_histories` `[BD recommended]`
`id`, `booking_id` FK, `from_status` (null for the initial row), `to_status`, `changed_by` FK users null (null = system/customer), `note` null, `created_at`. **Append-only** — never update or delete. A row is written on creation (null → pending) and on every status change.

### 5.4 `contact_messages` `[BD]`
`id`, `name`, `email`, `phone` null, `subject` null, `message` text, `is_read` bool default false, `read_at` null, `created_at`.

### 5.5 `payments` `[BD][P]`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| booking_id | FK bookings | |
| receipt_number | string unique | e.g. `RCT-2026-00001`, sequential |
| amount | unsigned bigint | UGX, > 0 |
| method | enum(`cash`,`mtn_momo`,`airtel_money`,`bank_transfer`,`other`) | confirm list with owner |
| reference | string null | mobile-money transaction ID / bank ref |
| received_at | datetime | when money was received |
| recorded_by | FK users | |
| note | string null | e.g. "deposit" |
| status | enum(`confirmed`,`voided`) | default `confirmed` |
| voided_by, voided_at, void_reason | null | |

**Never hard-delete payments.** Mistakes are **voided** with a reason (kept for audit).

### 5.6 `notification_logs` `[NEW]`
`id`, `booking_id` null, `event` (e.g. `booking_submitted`, `booking_approved`, `booking_rejected`, `payment_recorded`), `recipient_type` (`admin`/`customer`), `channel` (`mail`/`whatsapp`/`database`), `recipient` string, `status` (`queued`/`sent`/`failed`), `error` text null, `sent_at` null, `created_at`. Lets the admin see what was actually delivered.

### 5.7 `booking_conflict_attempts` `[NEW]`
`id`, `customer_name`, `customer_phone`, `customer_email` null, `event_type`, `requested_date`, `created_at`. See 2.1.

### 5.8 Relationships
User 1—* Booking · Booking 1—* StatusHistory · Booking 1—* Payment · User 1—* Payment (recorded_by).

---

## 6. Business rules

### 6.1 Booking lifecycle
```
            ┌──────────► APPROVED ──(admin only, reason required)──► REJECTED
 PENDING ───┤
            └──────────► REJECTED
```
- A booking is created as **Pending**.
- Only `admin` can move Pending → Approved/Rejected `[BD]`. (Owner may later allow staff — Section 13.)
- Rejection **requires** `decision_note`.
- `Approved → Rejected` is allowed for admin only (cancellation), with a reason; it is logged like any other change. `[NEW]`
- Every change: update booking + insert history row + queue notifications, **in one DB transaction** (notifications dispatched *after commit*).
- No other transitions. `Rejected → anything` is not allowed; the customer submits a new booking.

### 6.2 Booking reference
- Format: `LKB-` + 8 characters from an unambiguous alphabet (`23456789ABCDEFGHJKMNPQRSTUVWXYZ`, no `0/O/1/I/L`), e.g. `LKB-7K3QX9MD`.
- Generated with a cryptographically secure random source — **not sequential**, so references can't be guessed.
- `unique` constraint; on collision, regenerate and retry.

### 6.3 Availability
See 2.1. Implement as a single `BookingAvailability` service used by: submission, approval, the public availability endpoint, and the admin conflict flag.

### 6.4 Payments and balances
- `quoted_amount` is set on the booking by admin/staff (usually when approving).
- **Payments can only be recorded on Approved bookings with a quoted amount.**
- `amount_paid = SUM(payments.amount WHERE status = confirmed)`
- `balance = quoted_amount − amount_paid` — **calculated**, never stored.
- `payment_status` (derived): `unpaid` (paid = 0) · `partial` (0 < paid < quote) · `paid` (balance = 0).
- A payment that would make `amount_paid > quoted_amount` is **rejected** (422). Changing a quote below what is already paid is also rejected.
- Each recorded payment gets a sequential `receipt_number`. Receipt = printable page (print-optimised HTML first; PDF optional later) showing business name, receipt no., booking reference, customer, amount, method/reference, date, running balance.

### 6.5 Permissions matrix

| Action | Guest | Customer | Staff | Admin |
|---|:--:|:--:|:--:|:--:|
| Register / log in | ✔ | — | — | — |
| Submit booking | ✔ | ✔ | ✔ (on behalf) | ✔ |
| Track by reference (status only) | ✔ | ✔ | ✔ | ✔ |
| View own bookings + payments | — | ✔ (own only) | — | — |
| View all bookings, calendar | — | — | ✔ | ✔ |
| Approve / reject | — | — | ✖ (default) | ✔ |
| Set / edit quote | — | — | ✔ | ✔ |
| Record payment, print receipt | — | — | ✔ | ✔ |
| Void payment | — | — | ✖ | ✔ |
| Contact messages | send only | send only | read | read/manage |
| Reports | — | — | view | view + export |
| Create / disable staff accounts | — | — | — | ✔ |

Enforce with route middleware **and** Policies; a customer must never be able to read another customer's booking by changing an ID or reference.

---

## 7. API specification

Prefix JSON routes with `/api` (adapt to Inertia if the frontend requires — keep the same actions/validation). All write routes are CSRF-protected for session auth. Field names below are the **defaults**; align them with the frontend per Section 10.

### 7.1 Auth `[BD]`

| Method & path | Auth | Purpose |
|---|---|---|
| `POST /api/auth/register` | guest | Create customer account |
| `POST /api/auth/login` | guest | Start session |
| `POST /api/auth/logout` | any | End session (invalidate + regenerate CSRF token) |
| `GET /api/auth/me` | any | Current user + role (frontend uses this to route/guard pages) |

**Register** — `name` (req, 2–100), `email` (req, valid, unique), `phone` (req, valid UG number), `password` (req, min 8, confirmed if the form has a confirm field). Role is forced to `customer`. Success → 201 + message; the customer then logs in (or is logged in — follow the frontend flow). Duplicate email → 422 on `email`.

**Login** — `email`, `password`. Failure → 422/401 with the **generic** message "Incorrect email or password" (never say which was wrong). Regenerate the session on success. Throttle: 5 attempts/min per email+IP. Inactive accounts cannot log in.

### 7.2 Public booking features

| Method & path | Auth | Purpose |
|---|---|---|
| `GET /api/availability?month=YYYY-MM` | public | List of **full dates** in that month |
| `POST /api/bookings` | public (user linked if logged in) | Submit booking |
| `POST /api/bookings/track` (or `GET /api/bookings/track/{reference}`) | public, throttled | Look up status by reference |
| `POST /api/contact` | public, throttled | Send contact message |

**Submit booking — validation (server-side) `[BD]`**

| Field | Rule |
|---|---|
| `customer_name` | required, 2–100 chars |
| `email` | required, valid email |
| `phone` | required, valid Uganda number |
| `whatsapp_number` | optional, valid Uganda number (UI may offer "same as phone") |
| `event_type` | required, one of the allowed list |
| `event_date` | required, valid date, **≥ today + `MIN_LEAD_DAYS`** (default `1`), ≤ today + 2 years, **not a full date** (2.1) |
| `event_location` | required, 3–255 chars |
| `guest_count` | required, integer, 1 – `MAX_GUESTS` (default `5000`) |
| `extra_requirements` | optional, ≤ 2000 chars |

Behaviour: create with status `pending`, generate `reference`, write first history row, queue admin notification + customer acknowledgement email, return **201** with `{ reference, status, event_date, event_type }` so the UI can show "Your booking reference is …". Throttle 5/min/IP. Add a hidden honeypot field check to cut spam.

**Track booking `[BD]`** — input: reference (case-insensitive, trimmed). Return **only**: `reference`, `status` (`pending|approved|rejected`), `event_type`, `event_date`, `status_changed_at`, and the `decision_note` **only when rejected** (so the customer knows why). Return **no** names, phones, emails, locations or money. Unknown reference → 404 with the same generic message each time ("We couldn't find a booking with that reference. Please check and try again.") — never reveal anything about other customers. Throttle 10/min/IP to prevent guessing.

**Contact `[BD]`** — `name` (req), `email` (req, valid), `phone` (opt), `subject` (opt, ≤150), `message` (req, 10–2000). Save, return 201 with confirmation text. Queue an email to the admin (non-blocking).

### 7.3 Customer area

| Method & path | Auth | Purpose |
|---|---|---|
| `GET /api/my/bookings` | customer | Own bookings (paginated) |
| `GET /api/my/bookings/{reference}` | customer (owner only) | Detail + `quoted_amount`, `amount_paid`, `balance`, `payment_status`, list of confirmed payments `[P]` |

### 7.4 Admin / staff area (`role:admin,staff` unless stated)

| Method & path | Role | Purpose |
|---|---|---|
| `GET /api/admin/dashboard` | admin, staff | Live totals: `total_bookings`, `pending`, `approved`, `rejected`, `upcoming_30_days`, `unread_messages`, `total_outstanding_balance`. Computed by query on each call (no stale cache) `[BD]` |
| `GET /api/admin/bookings` | admin, staff | List; filters `status`, `q` (name/phone/reference), `from`, `to`, `source`; sort; paginate |
| `POST /api/admin/bookings` | admin, staff | Enter phone/walk-in booking on behalf of customer (`source` required; email optional) `[P]` |
| `GET /api/admin/bookings/{id}` | admin, staff | Full detail + status history + payments summary + `has_date_conflict` |
| `PATCH /api/admin/bookings/{id}` | admin, staff | Edit details/quote (only while Pending, or quote while Approved) |
| `PATCH /api/admin/bookings/{id}/status` | **admin** | Body `{status: approved\|rejected, note?, quoted_amount?}`; see 6.1 |
| `GET /api/admin/calendar?month=YYYY-MM` | admin, staff | Approved (and optionally pending) bookings per day `[P]` |
| `GET /api/admin/conflict-attempts` | admin | See 2.1 |
| `GET /api/admin/bookings/{id}/payments` | admin, staff | Payment ledger for a booking |
| `POST /api/admin/bookings/{id}/payments` | admin, staff | Record payment (6.4) |
| `POST /api/admin/payments/{id}/void` | **admin** | Void with reason |
| `GET /api/admin/payments/{id}/receipt` | admin, staff | Printable receipt |
| `GET /api/admin/payments-overview` | admin, staff | "Payments and Balances" screen: approved bookings with quote / paid / balance / status; filter by status, date range, "outstanding only" |
| `GET /api/admin/messages` · `PATCH /api/admin/messages/{id}/read` | admin (staff read) | Contact inbox |
| `GET /api/admin/reports/bookings` · `/reports/payments` | admin, staff | Date-range reports; `?format=csv` for admin `[P]` |
| `GET/POST/PATCH /api/admin/staff` | **admin** | Create, list, disable staff accounts |

**Admin account:** created by a **seeder/artisan command that reads credentials from `.env`** (e.g. `php artisan lekobosi:create-admin`). No default password committed to the repo. `[BD: "authorised admin account"]`

---

## 8. Notifications `[BD][P]`

Implement with Laravel Notifications + queue. One class per event, each declaring its channels. Write a `notification_logs` row for every attempt.

| Event | Recipient | Channels (now) | Channels (later) |
|---|---|---|---|
| `booking_submitted` | Admin (`ADMIN_NOTIFY_EMAIL`) | mail + dashboard badge | + WhatsApp |
| `booking_received` | Customer (acknowledgement incl. reference) | mail | + WhatsApp |
| `booking_approved` | Customer | mail | + WhatsApp |
| `booking_rejected` | Customer (includes reason) | mail | + WhatsApp |
| `payment_recorded` | Customer (amount, receipt no., balance) `[P]` | mail | + WhatsApp |
| `contact_message` | Admin | mail | — |

**Rules**
- Dispatch **after the DB transaction commits** (`afterCommit`).
- A failed notification must **never** fail or roll back the booking/status change. Log the failure and continue. `[BD]`
- If the customer gave no email (phone/walk-in booking), skip mail and log "no channel".
- **WhatsApp channel (later):** build a `WhatsAppChannel` behind an interface, driven by config (`WHATSAPP_ENABLED=false` by default; provider credentials in `.env`). Business-initiated messages need pre-approved templates; keep template names/params in config. Do not enable until the owner has an account. Do not claim WhatsApp is "done" in docs until it is actually working. `[P]`
- **`wa.me` helper (now):** admin booking detail returns `whatsapp_link` — `https://wa.me/<number>?text=<url-encoded prefilled message>` — for approve/reject so staff can send with one tap. Message text lives in config/lang files.

---

## 9. Security requirements `[BD]`

Checklist the agent must satisfy and test:

- [ ] Passwords hashed with Laravel `Hash` (bcrypt/argon2id). Never stored or logged in plain text.
- [ ] **All** validation on the server (Form Requests), regardless of browser checks.
- [ ] Role-based access: middleware + Policies; tests prove a customer gets **403** on every `/api/admin/*` route and cannot read other customers' bookings.
- [ ] No raw SQL with interpolated input; Eloquent/query builder bindings only.
- [ ] Mass-assignment protection (`$fillable`); `role`, `status`, `decided_by`, `reference` can never be set from request input on public routes.
- [ ] CSRF protection on state-changing routes; session regenerated on login, invalidated on logout; cookies `HttpOnly`, `Secure` (when HTTPS), `SameSite=Lax`.
- [ ] Rate limiting: login, register, booking submit, track, contact.
- [ ] Output escaping is the frontend's job (React escapes by default) — do **not** store HTML; strip tags from free-text fields on input.
- [ ] CORS restricted to the frontend origin(s) (only if SPA is on a separate origin).
- [ ] No DB credentials, API keys or private data in source files, responses or logs. `APP_DEBUG=false` in production. Don't leak stack traces.
- [ ] Track endpoint returns only the minimal fields in 7.2 and identical errors for not-found.
- [ ] HTTPS enforced in production; HSTS header.
- [ ] Personal data minimised and access-limited (Uganda Data Protection and Privacy Act, 2019). `[P]` Phone/email never written to application logs.
- [ ] Admin actions that change data are attributable (`decided_by`, `recorded_by`, `voided_by`, history rows).

---

## 10. Frontend integration (Step 0 — do this before writing code)

The students built the frontend; its structure is unknown to this spec. Before Phase 1, the agent must:

1. Locate the frontend (repo/folder) and identify: framework/bundler, router, how it calls the server (fetch/axios/Inertia), where API base URL is configured.
2. List every **page and form**: Register, Login, Booking, Track Booking, Contact, Admin Dashboard (incl. **Payments and Balances**), and any others (customer dashboard, calendar, messages…).
3. For each form, record **exact field names, input types, dropdown option values** (especially `event_type`) and any client-side validation. Copy findings into `docs/FRONTEND_NOTES.md`.
4. Decide the integration mode and write it down:
   - **Separate SPA + Laravel API** → Sanctum SPA cookie auth, CORS, `SANCTUM_STATEMENT_DOMAINS`, shared parent domain in production.
   - **Frontend merged into Laravel (Inertia/Vite)** → session auth; controllers return Inertia responses but call the same Actions.
5. Note every place where the frontend currently uses **mock/hard-coded data** (dashboard totals, booking lists, payments) — each one becomes a backend task.
6. List gaps: screens in this spec with no UI yet (e.g. staff booking entry, calendar, receipts, reports, conflict attempts). Report them; **do not build UI** unless asked.

---

## 11. Implementation phases and acceptance criteria

Work top to bottom. Each phase ends with passing tests and a short commit message summarising it.

**Phase 0 — Setup & discovery**
Laravel project/config, MySQL connection, `.env.example`, timezone, queue + mail config, CORS/Sanctum per Step 0, `docs/FRONTEND_NOTES.md`. *Done when:* app boots, migrations run on a clean DB, frontend can reach `GET /api/auth/me`.

**Phase 1 — Auth & roles**
Users migration, register/login/logout/me, role middleware, admin seeder command, throttling. *Done when:* register→login→logout works from the UI; duplicate email rejected; wrong password gives the generic error; customer gets 403 on an admin route; passwords are hashed in DB.

**Phase 2 — Booking submission & availability**
Bookings + history + conflict-attempts migrations, reference generator, `BookingAvailability`, `POST /api/bookings`, `GET /api/availability`. *Done when:* a valid booking is stored as Pending with a unique reference and a history row; invalid input returns field errors; a full date is rejected and logged in `booking_conflict_attempts`; UI shows the reference.

**Phase 3 — Track booking**
*Done when:* valid reference shows status; unknown reference gives generic 404; response contains none of the private fields; throttle triggers on repeated attempts.

**Phase 4 — Admin dashboard & decisions**
Dashboard totals, list/filter/detail, approve/reject (transactional, re-checks availability), history rows, `has_date_conflict`, calendar. *Done when:* totals match the DB; approving/rejecting persists status + timestamp + history; double-approve for a full date returns 409; customer's tracking page shows the new status; staff cannot call the status endpoint.

**Phase 5 — Contact messages**
*Done when:* valid message saved + confirmation returned; admin can list and mark read.

**Phase 6 — Notifications**
Notification classes, queue, `notification_logs`, `wa.me` link in booking detail, admin unread badge. *Done when:* a submission emails admin and customer; approve/reject emails customer; killing the mail server does **not** break booking or status change (failure is logged).

**Phase 7 — Payments & balances**
Quote on booking, payments ledger, balance maths, void, receipts, payments-overview, customer-side balance view. *Done when:* the rules in 6.4 hold under tests (instalments, overpayment rejected, void restores balance, payment on non-approved booking rejected); receipt prints; Payments and Balances screen shows real data.

**Phase 8 — Staff entry, reports, staff accounts**
Phone/walk-in booking entry, staff management, booking and payment reports + CSV. *Done when:* a staff user can create a phone booking with no email, record a payment, and run a report; admin can disable a staff account.

**Phase 9 — Hardening & handover**
Walk the Section 9 checklist, add missing tests, seed demo data (fake), write `README.md` (setup, env vars, deploy, queue/cron), production config review. *Done when:* full test suite green; checklist all ticked; fresh clone can be set up from the README.

---

## 12. Testing `[P]`

Three levels, matching the proposal:

- **Unit:** reference generator, phone normaliser, availability service, balance/payment-status calculation, status-transition rules.
- **Integration (feature tests):** registration/login, booking submit (valid/invalid/full date), tracking privacy, approve→history→notification chain, role access (customer vs staff vs admin on every admin route), payment recording and voiding, contact form.
- **User acceptance:** manual script for owner + staff + a few customers using sample bookings: submit → approve → record deposit → record balance → print receipt → check report. Provide the script in `docs/UAT.md`.

Seed data: factories for users/bookings/payments with **fake** names and `+2567…` numbers.

---

## 13. Open questions and default assumptions

The agent proceeds with the **default** and flags these in `docs/FRONTEND_NOTES.md`/README so the owner can change them. They are env/config values wherever possible.

| # | Question for the owner | Default used |
|---|---|---|
| 1 | Can Lekobosi cater for more than one event on the same date? | `MAX_EVENTS_PER_DAY=1` |
| 2 | Minimum notice before an event? | `MIN_LEAD_DAYS=1` |
| 3 | Maximum guests per booking? | `MAX_GUESTS=5000` |
| 4 | Must customers be logged in to book? | **No** — guests may book; if logged in the booking is linked to the account |
| 5 | Exact list of event types? | Taken from the frontend dropdown |
| 6 | May **staff** approve/reject, or owner only? | Owner (`admin`) only |
| 7 | Should customers verify their email on registration? | Off (can be enabled later) |
| 8 | Payment methods to offer? | cash, MTN MoMo, Airtel Money, bank transfer, other |
| 9 | Is a minimum deposit required before confirming? | No rule enforced; deposit is just a payment |
| 10 | Which email service for notifications? | SMTP from `.env` |
| 11 | WhatsApp provider/account and number? | Not enabled; `wa.me` links only |
| 12 | Hosting (shared hosting vs VPS)? Can a queue worker run? | Assume cron-driven queue |
| 13 | Is the Approved → Rejected (cancellation) path wanted? | Yes, admin only, reason required |
| 14 | Is a CAPTCHA wanted on the public booking/contact forms? | No — throttling + honeypot field only |

---

## 14. Environment variables (reference)

```
APP_NAME="Lekobosi Catering"
APP_ENV=local
APP_DEBUG=true            # false in production
APP_URL=
APP_TIMEZONE=Africa/Kampala

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false   # true in production (HTTPS)
SANCTUM_STATEFUL_DOMAINS=     # only if separate SPA
FRONTEND_URL=                 # CORS, if separate SPA

QUEUE_CONNECTION=database
MAIL_MAILER=log               # smtp in production
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Lekobosi Catering"

ADMIN_NOTIFY_EMAIL=
ADMIN_WHATSAPP_NUMBER=        # used for wa.me link / later API
ADMIN_SEED_NAME=
ADMIN_SEED_EMAIL=
ADMIN_SEED_PASSWORD=          # used once by the create-admin command; never commit

MAX_EVENTS_PER_DAY=1
MIN_LEAD_DAYS=1
MAX_GUESTS=5000

WHATSAPP_ENABLED=false
WHATSAPP_PROVIDER=
WHATSAPP_TOKEN=
WHATSAPP_PHONE_ID=
```

---

## 15. Glossary

- **Reference** — the public booking code (e.g. `LKB-7K3QX9MD`) a customer uses to track a booking.
- **Quote** — agreed price for the event, in UGX, stored on the booking.
- **Balance** — quote minus confirmed payments.
- **Full date** — a date where approved bookings have reached `MAX_EVENTS_PER_DAY`.
- **Walk-in / phone booking** — a booking entered by staff on behalf of a customer who didn't use the website.
