# Lekobosi Catering Booking System — Backend

Backend for a web-based catering booking system for **Lekobosi Restaurant and Outside Catering Services** (Kyankwanzi, Uganda). The React frontend already exists (built by MUBS students). Your job is the backend.

**The full specification is in @docs/BACKEND_SPEC.md — read it completely before writing code.** It is the source of truth.

## Stack
PHP + Laravel (latest stable), MySQL, session auth (Sanctum SPA or starter-kit auth — decided in Step 0), Laravel queues for notifications. React frontend (existing).

## Start here
1. Do **Step 0 / Section 10** of the spec: inspect the frontend, record exact field names, routes and dropdown values in `docs/FRONTEND_NOTES.md`, and choose the integration mode. Do this before any code.
2. Then follow the phases in Section 11 **in order**; finish each phase's acceptance criteria and tests before the next.

## Non-negotiables
- The owner's approve/reject decision is required; bookings are never auto-confirmed.
- A date is blocked only by **Approved** bookings; full-date submissions are rejected (422) and logged in `booking_conflict_attempts`. Re-check inside a locked transaction on approval.
- Payments are **recorded manually** (no mobile-money/card integration). Balance = quote − confirmed payments, always calculated, never stored. Void payments, never delete them.
- Notifications are queued, sent after commit, and must never break a booking or status change. WhatsApp is a later pluggable channel; use `wa.me` links for now.
- Track-by-reference returns only status + minimal fields, with an identical generic error when not found. Rate-limit it.
- Server-side validation everywhere; hashed passwords; role middleware **and** policies; no secrets in git; `role` is never mass-assignable from public input.
- Money = UGX unsigned integers. Phones normalised to E.164. Timezone `Africa/Kampala`. Public ID = booking `reference`, never the numeric id.
- Business logic in Action/Service classes; controllers stay thin.

## Conventions
- Form Requests for validation, Policies for authorization, API Resources for output, backed enums for statuses/roles.
- Frontend naming wins for field names; spec wins for business rules. Log every mismatch in `docs/FRONTEND_NOTES.md`.
- Small, focused commits per task. Tests with every feature (Pest/PHPUnit — follow what the repo uses). Fake data only in seeders/tests.

## Do not
- Build anything in the spec's **Out of scope** list (Section 3).
- Guess an unanswered business rule: use the default in Section 13, or ask.
- Redesign or rewrite the frontend. Report UI gaps instead.
