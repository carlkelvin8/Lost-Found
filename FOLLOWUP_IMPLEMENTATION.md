# Follow-up implementation: student finder details and simultaneous accounts

October 14, 2026 — Asia/Manila

> Latest correction: finder names now also display for regular-user Found reports, new self-reported finder identities are snapshotted, and the dashboard Claimed label is restored. See [FINDER_DISPLAY_FIX.md](FINDER_DISPLAY_FIX.md) for the current verification (128 PHP tests, 480 assertions).

This completes the gaps found against the client's revised eight-item list. Changes are implemented locally. No application/production database migration or deployment was performed. Synthetic browser/HTTP records were created only in the explicitly isolated `client-followup.sqlite` fixture database.

## 1. Root Cause Analysis

- The earlier requirement #4 covered only a reporter name for Contact Override. The revised requirement additionally needs a student's name and student ID when Admin/OSA reports an item surrendered by that student. The earlier code had no independent finder identity fields or surrender workflow.
- The initial independent-session implementation separated one admin boundary from one user boundary. Two users signing in within the same boundary still shared their identity. The revised simultaneous-account requirement needed separately scoped server sessions beyond roles.
- Integration testing exposed that a session context route parameter could otherwise be passed as a controller's report ID. The context is now consumed by session middleware before the existing controller argument dispatcher runs.

## 2. Changes Implemented

**Student surrender reporting**

- Admin/OSA Found reports provide an “Item surrendered by a student” option.
- When selected, both the finder’s full name and student ID are required, even with an empty Contact Override.
- Ordinary and Unicode whitespace-only values, missing fields, invalid ID characters, and overlength values are rejected by backend validation. The client also validates required fields and whitespace.
- IDs are stored as strings, preserving leading zeros. The finder does not need to have a registered account.
- The authenticated admin remains `reporter_user_id`; finder details are independent of ownership, matching and claim permissions.
- Finder information is displayed only to staff. Matched claimants cannot see either the finder’s name or student ID.
- Lost reports, regular-user reports and staff self-found reports retain their prior requirements. Regular users and Lost reports cannot forge a student surrender.
- Existing report locks remain. The same fields are available in permitted legacy edits; a forged Lost type cannot bypass a stored Found report's validation.
- Repeated requests retain the existing submission idempotency behavior.

**Independent accounts in tabs**

- “Open another account” is available on the login screen and in the signed-in user menu.
- Each use opens a new login context with its own server session cookie, cookie path, authentication guard and remember-cookie namespace.
- Accounts may have the same role. Real HTTP and browser checks exercised two students alongside an admin in one shared browser/cookie jar.
- Logging out of one student context does not sign out the other student or admin.
- The route UUID is a public context selector, not a credential. Without that context's authenticated HttpOnly cookie, the UUID alone returns an unauthenticated response.
- CSRF tokens remain bound to their individual sessions. Backend role checks still deny students staff endpoints.
- Existing `/login` and `/admin/login` continue working. Navigation, report actions, polling and offline endpoints remain within the active context.
- Use “Open another account” for a separate identity. Simply duplicating an existing tab intentionally retains that same context and identity.

The other six implemented fixes remain present, and the complete regression suite was executed again.

## 3. Modified Files

- [app/Http/Controllers/ItemReportController.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Controllers/ItemReportController.php) — Expose the staff finder form; persist student surrender details separately from the submitting account.
- [app/Http/Middleware/IsolateAdminSession.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Middleware/IsolateAdminSession.php) — Create unique server session and remember-cookie namespaces per new account context, scope cookies, URLs and guards; remove the routing context before controller dispatch.
- [app/Http/Middleware/SecurityHeaders.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Middleware/SecurityHeaders.php) — Apply no-store headers to independent session pages.
- [app/Http/Requests/ItemReportRequest.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Requests/ItemReportRequest.php) — Validate authorized student surrenders, trim ordinary/Unicode whitespace, require finder name and student ID, validate the stored report type on edits, and enforce submission UUIDs in independent sessions.
- [app/Models/ItemReport.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Models/ItemReport.php) — Persist and cast new finder fields.
- [public/js/advanced-ui.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/advanced-ui.js) — Keep breadcrumbs inside the current account context without displaying session-routing identifiers.
- [public/sw.js](C:/Users/PC/Documents/lf_ai/lost-found/public/sw.js) — Recognize safely scoped offline endpoints and invalidate previous static caches.
- [resources/views/auth/login.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/auth/login.blade.php) — Add Open another account in a separate tab.
- [resources/views/components/layout/navbar.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/components/layout/navbar.blade.php) — Add Open another account to the signed-in menu.
- [resources/views/components/layout/sidebar.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/components/layout/sidebar.blade.php) — Preserve active styling in independent session routes.
- [resources/views/layouts/app.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/layouts/app.blade.php) — Scope interactive application URLs to the request session prefix.
- [resources/views/reports/create.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/reports/create.blade.php) — Include staff finder fields and their validation script.
- [resources/views/reports/edit.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/reports/edit.blade.php) — Include finder fields and validation for legacy editable reports.
- [resources/views/reports/show.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/reports/show.blade.php) — Display finder name and student ID to staff only.
- [routes/web.php](C:/Users/PC/Documents/lf_ai/lost-found/routes/web.php) — Add a new-account entry and UUID-scoped routes while retaining the standard user/admin boundaries and business authorization.
- [tests/Support/http-audit.py](C:/Users/PC/Documents/lf_ai/lost-found/tests/Support/http-audit.py) — Extend live HTTP tests to three accounts, two with the same role, scoped remember cookies, CSRF, authorization and student surrender persistence.
- [tests/Support/prepare-browser-audit.php](C:/Users/PC/Documents/lf_ai/lost-found/tests/Support/prepare-browser-audit.php) — Prepare a second explicitly isolated database and a second student test account without overwriting the prior fixture database.
- [database/migrations/2026_10_14_000002_add_student_finder_details.php](C:/Users/PC/Documents/lf_ai/lost-found/database/migrations/2026_10_14_000002_add_student_finder_details.php) — Add nullable finder identity columns and a false-by-default student surrender indicator, with rollback.
- [public/js/finder-details.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/finder-details.js) — Enable and require finder fields only in the applicable Found/student workflow; retain entered values across type changes.
- [resources/views/reports/partials/finder-details.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/reports/partials/finder-details.blade.php) — Shared staff-only student surrender form, independent of Contact Override.
- [tests/Feature/StudentFinderAndSessionTest.php](C:/Users/PC/Documents/lf_ai/lost-found/tests/Feature/StudentFinderAndSessionTest.php) — 11 integration/regression tests for names, IDs, permissions, privacy, retries, immutable type validation, scoped sessions, route parameter handling and migration rollback.
- [tests/js/finder-details.test.cjs](C:/Users/PC/Documents/lf_ai/lost-found/tests/js/finder-details.test.cjs) — Four client validation tests, including whitespace, Lost/self-found behavior and preserving values across context switches.

## 4. Database Changes

New migration: `2026_10_14_000002_add_student_finder_details.php`.

- `item_reports.surrendered_by_student`: boolean, default false.
- `item_reports.finder_name`: nullable string(190).
- `item_reports.finder_student_id`: nullable string(60).

Existing records are not assigned fabricated finder identities. Migration rollback/reapplication was tested while preserving existing report rows. Rollback drops the new fields and their values. This migration and the earlier audit migration must be applied to the intended database before release; only isolated test databases were migrated here.

## 5. Actual Test Results

- **PHP:** `php artisan test` — **120 tests passed, 447 assertions, 0 failures.**
- **JavaScript:** `node --test tests/js/*.test.cjs` — **10 tests passed, 0 failures.**
- **Real HTTP:** `python tests/Support/http-audit.py client-followup.sqlite` — **8 checks passed**: original admin/user cookies; invalid/cross-boundary CSRF; eight overlapping retries producing one report; isolated user logout; three independent contexts with two students and separate remember cookies; scoped CSRF/permissions/anonymous-context denial; one-student logout isolation; scoped Admin student-surrender validation, saving, viewing and deduplication.
- **Browser:** Admin Found/student mode displays both finder fields; whitespace student ID blocks submission; an image preview and finder values survive server-side description validation; completing the description submits the same image; finder values persist after refresh.
- **Browser:** Two students and Admin coexist in three tabs. Refresh preserves the original student's identity. Logging out of the second student leaves the first student and Admin authenticated. The new account link opens another login tab.
- **Build:** `npm run build` passed. Vite's existing empty app chunk warning remains informational; public scripts were separately syntax-checked.
- **Syntax/layout compilation:** changed/new PHP `-l`, changed/new JavaScript `node --check`, `php artisan view:cache`, and `git diff --check` passed.

One live integration failure was caught during development (the UUID reached the report ID argument); it was fixed, covered by a regression test, and the eight HTTP checks passed on rerun.

## 6. Remaining Verification / Release Requirements

- Physical webcam/phone capture and simultaneous MySQL transactions under multiple workers still require the device and production-style database environment. Camera File handling, validation retention, SQL uniqueness, overlapping HTTP requests, and queue/notification deduplication have automated coverage. No physical-camera or MySQL load-test result is claimed.
- Apply the migrations through the normal release process before using this code with the intended database. Production records and schema were not modified here.
- The independent-account feature requires using its new-account entry. Tabs opened at the same ordinary `/login`, `/admin/login`, or copied session URL share that specific boundary/context.
- HTTPS Secure-cookie delivery, long-term remember-me expiration, real SMTP and external AI analysis were not exercised in these isolated tests.

## 7. Final Status Against the Revised List

| # | Requirement | Status |
| --- | --- | --- |
| 1 | Saved rejection notes visible to Admin and claimant | FIXED AND VERIFIED (local tests and previous browser verification) |
| 2 | Duplicate Lost/Found report prevention | IMPLEMENTED, PENDING VERIFICATION of MySQL multi-worker concurrency; local retry/deduplication tests passed |
| 3 | Manual match search | FIXED AND VERIFIED (local regression tests) |
| 4 | Admin reports student-surrendered item with finder name + student ID | FIXED AND VERIFIED (backend, client tests, live HTTP and browser) |
| 5 | Captured image retained after validation | IMPLEMENTED, PENDING VERIFICATION on physical camera; File/capture component and browser-upload retention tests passed |
| 6 | Cellphone landing label | FIXED AND VERIFIED (local regression test) |
| 7 | Correct successful Claimed Items count | FIXED AND VERIFIED (local regression tests) |
| 8 | Different accounts used simultaneously, including two regular users | FIXED AND VERIFIED (live shared-cookie HTTP and same-browser tab tests) |

Browser evidence uses synthetic test data:

![Saved student finder name and ID in authorized Admin report details](C:/Users/PC/Documents/lf_ai/lost-found/storage/framework/testing/student-finder-proof.png)
