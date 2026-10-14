# Lost & Found: client issue audit and implementation report

Date: October 14, 2026 (Asia/Manila)

All eight code changes are implemented. Six issues are verified in the available local environment. Issues 2 and 5 have additional environment-specific checks outstanding, detailed below. No commits, pushes or deployments were performed. The working tree was clean before changes. The configured application database and production records were not modified; migrations and fixtures ran only in isolated SQLite databases.

## 1. Root Cause Analysis

| Issue | Confirmed code defect |
| --- | --- |
| 1. Rejection notes | The rejection form had only a confirmation button; no reason field, validation, model attribute or database column existed. Review metadata existed, but the pending-status check occurred outside the transaction. Browser testing also caught that a modal inside the animated grid/clipped card could not receive clicks; the delivered modal is outside those containers. |
| 2. Duplicate reports | The form had no in-flight submission guard and the controller inserted on every request without a submission identifier or database uniqueness guarantee. Transactions alone do not deduplicate separate requests. The exact historical client trigger cannot be reconstructed from source alone. |
| 3. Manual matching | The Matches page provided numeric ID pairing only; no manual report-search endpoint or result view existed. Pairing also overwrote the score/method of existing automatic matches. |
| 4. Reporter name | Contact Override was a single optional contact field, with no distinct name field, persistence or conditional requirement. |
| 5. Lost camera image | Ordinary server validation redirected to a fresh Blade form. Browsers do not restore File inputs via Laravel old input. Camera Done already copied captured Blob data correctly into the File input; the page replacement discarded that input. |
| 6. Landing label | Both sample phone cards hardcoded iPhone 14 Pro. |
| 7. Claimed count | Report status claimed means a pending claim application; approval sets the claim to approved and the report to returned. Counting reports still in claimed does not count completed claims. Existing report aggregates are also cached. |
| 8. Shared identity | Both tabs used the same web guard, server session cookie, remember cookie and session record. Existing JavaScript endpoints and service-worker caches also assumed a single identity. |

## 2. Changes Implemented

1. Added a required, trimmed, length-limited rejection reason and a confirmation modal. Reasons survive reload and are visible to the claimant and authorized staff. Existing reviewer/timestamp fields are retained. Approval, rejection and cancellation now lock and recheck state. Rejection reasons remain out of finder-visible report history. Finalized report statuses cannot be reopened by a legacy pending claim.
2. Added a per-form UUID, a single in-flight frontend request, disabled/loading submit button, a backend account-row lock and a unique account/UUID index. Replays return the existing report and skip uploads, matching, audit and AI-job side effects. New UUIDs permit deliberately separate reports with identical contents. Failed transactions clean up their new upload files; notification email is sent after commit.
3. Added staff-only manual search over active pending/matched reports, with literal text, report type, category and location filters. Results show item details, links, pagination and explicit empty states. Manual pairing rejects existing pairs and terminal reports. Existing scoring algorithms and thresholds are unchanged.
4. Added reporter_name to creation and legacy edit forms. A Found report with a nonempty Contact Override requires a trimmed name on both client and server. Lost reports and Found reports without an override retain their existing requirements. Staff report details display the name.
5. Report creation submits FormData asynchronously and renders 422 errors on the same page. Camera/upload File objects remain in the form across validation and network errors. The same identifier and image are reused on retry. Photos can be deliberately removed/replaced; obsolete preview callbacks cannot resurrect removed selections. No image drafts are added to persistent browser storage by this workflow.
6. Replaced only the two landing sample labels with Cellphone. Report content, brand fields and stored data are untouched.
7. Added a separate Claimed Items metric using COUNT(DISTINCT report_id) for approved claims. Pending/rejected/cancelled claims are excluded. This metric is read live on page load and through dashboard polling. The transitional report-status bar is labeled Awaiting review to preserve accurate status semantics.
8. Added /admin/login and an /admin application boundary with a distinct admin guard, session cookie name/path, remember-cookie namespace and validated role checks. /login retains the normal user context. URLs, polling and navigation remain in their active boundary. CSRF, HttpOnly and configured Secure/SameSite behavior remain in place. Authenticated pages/API responses and report images are excluded from service-worker caching; offline replay is bound to its account and endpoint.

## 3. Modified Files

All paths below refer to the shared workspace. Generated build output, disposable SQLite fixtures, session files and screenshots are not application-source changes.

- [app/Http/Controllers/AuthWebController.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Controllers/AuthWebController.php) â€” Validate staff-boundary login; honor the existing Remember me checkbox.
- [app/Http/Controllers/ClaimController.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Controllers/ClaimController.php) â€” Persist rejection reasons; lock and recheck claim finalization; preserve finalized reports and correct active-match query grouping.
- [app/Http/Controllers/DashboardController.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Controllers/DashboardController.php) â€” Read the distinct approved report count without caching the successful-claim metric.
- [app/Http/Controllers/ItemReportController.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Controllers/ItemReportController.php) â€” Serialize report submissions, replay existing identifiers, suppress retry side effects, return JSON, save reporter names and clean failed uploads.
- [app/Http/Controllers/ReportMatchController.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Controllers/ReportMatchController.php) â€” Add authorized filtered manual search; reject existing and terminal manual pairs.
- [app/Http/Controllers/WebBaseController.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Controllers/WebBaseController.php) â€” Send notification email after transaction commit to prevent rollback/retry email duplication.
- [app/Http/Middleware/SecurityHeaders.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Middleware/SecurityHeaders.php) â€” Prevent authenticated and authentication responses from being cached.
- [app/Http/Requests/ItemReportRequest.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Requests/ItemReportRequest.php) â€” Require UUID identifiers on creation and trimmed reporter names on Found Contact Override.
- [app/Models/Claim.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Models/Claim.php) â€” Allow persistence of rejection_reason.
- [app/Models/ItemReport.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Models/ItemReport.php) â€” Allow persistence of submission_id and reporter_name.
- [bootstrap/app.php](C:/Users/PC/Documents/lf_ai/lost-found/bootstrap/app.php) â€” Install session-isolation and staff-boundary middleware in the proper order.
- [config/auth.php](C:/Users/PC/Documents/lf_ai/lost-found/config/auth.php) â€” Define an independent admin session guard using the existing user provider.
- [public/js/account-security.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/account-security.js) â€” Keep session-management requests in the current authentication boundary.
- [public/js/advanced-search.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/advanced-search.js) â€” Keep search navigation in the current authentication boundary.
- [public/js/advanced-ui.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/advanced-ui.js) â€” Keep dashboard breadcrumb navigation in the current authentication boundary.
- [public/js/batch-leaderboard.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/batch-leaderboard.js) â€” Scope bulk operations and leaderboard requests to the current boundary.
- [public/js/bookmarks-compare.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/bookmarks-compare.js) â€” Scope report links and comparison requests to the current boundary.
- [public/js/export-share.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/export-share.js) â€” Generate report sharing links for the current boundary.
- [public/js/form-validation.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/form-validation.js) â€” Allow the report creation handler to own its validation and submission.
- [public/js/pwa.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/pwa.js) â€” Scope requests and offline drafts by account and endpoint; preserve draft submission identifiers.
- [public/js/realtime.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/realtime.js) â€” Scope polling/presence requests and refresh the successful claimed-item counter.
- [public/js/wow-features.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/wow-features.js) â€” Scope keyboard navigation shortcuts to the current boundary.
- [public/sw.js](C:/Users/PC/Documents/lf_ai/lost-found/public/sw.js) â€” Cache only allowlisted public static assets, purge old caches and prevent cross-account offline draft replay.
- [resources/views/auth/login.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/auth/login.blade.php) â€” Expose the independent Admin/OSA sign-in entry and hide unsupported self-registration in that boundary.
- [resources/views/claims/show.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/claims/show.blade.php) â€” Add the required rejection modal outside clipped/animated cards; show persisted reason with authorized reviewer details.
- [resources/views/components/layout/sidebar.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/components/layout/sidebar.blade.php) â€” Preserve active navigation styling for namespaced admin routes.
- [resources/views/dashboard.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/dashboard.blade.php) â€” Display a dedicated Claimed Items total and distinguish Awaiting review from successful claims.
- [resources/views/landing.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/landing.blade.php) â€” Change the two sample iPhone labels to Cellphone.
- [resources/views/layouts/app.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/layouts/app.blade.php) â€” Provide the active URL prefix and account identity for existing interactive features.
- [resources/views/matches/index.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/matches/index.blade.php) â€” Expose manual report search from the existing Matches screen.
- [resources/views/reports/create.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/reports/create.blade.php) â€” Add submission identifier, conditional reporter name, error feedback and intentional photo removal; avoid stale asynchronous previews.
- [resources/views/reports/edit.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/reports/edit.blade.php) â€” Carry the report type and validate/save reporter names in legacy editable reports.
- [resources/views/reports/show.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/reports/show.blade.php) â€” Display override reporter names to staff only.
- [routes/web.php](C:/Users/PC/Documents/lf_ai/lost-found/routes/web.php) â€” Add manual search, approved-item refresh data and namespaced admin application routes.
- [app/Http/Middleware/IsolateAdminSession.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Middleware/IsolateAdminSession.php) â€” Select separate session cookie/path and guard before session startup; keep generated URLs within the active boundary.
- [app/Http/Middleware/RequireAdminBoundary.php](C:/Users/PC/Documents/lf_ai/lost-found/app/Http/Middleware/RequireAdminBoundary.php) â€” Enforce active Admin/OSA membership inside the admin boundary.
- [database/migrations/2026_10_14_000001_add_audit_workflow_fields.php](C:/Users/PC/Documents/lf_ai/lost-found/database/migrations/2026_10_14_000001_add_audit_workflow_fields.php) â€” Add nullable workflow fields and a per-account submission UUID unique index.
- [public/js/report-create.js](C:/Users/PC/Documents/lf_ai/lost-found/public/js/report-create.js) â€” One in-flight request; JSON validation feedback; retain photo Files and identifier across errors.
- [resources/views/matches/search.blade.php](C:/Users/PC/Documents/lf_ai/lost-found/resources/views/matches/search.blade.php) â€” Render search filters, authorized active report details, pagination and empty results.
- [tests/Feature/ClientReportedIssuesTest.php](C:/Users/PC/Documents/lf_ai/lost-found/tests/Feature/ClientReportedIssuesTest.php) â€” 19 regression tests covering all eight issues, authorization, privacy, notifications, filtering, finalization and migration rollback.
- [tests/Support/http-audit.py](C:/Users/PC/Documents/lf_ai/lost-found/tests/Support/http-audit.py) â€” Exercise real cookies, two identities, CSRF rejection, eight overlapping report retries and isolated logout against the fixture server.
- [tests/Support/prepare-browser-audit.php](C:/Users/PC/Documents/lf_ai/lost-found/tests/Support/prepare-browser-audit.php) â€” Create fixtures only in an explicitly isolated SQLite database, refusing to overwrite existing data.
- [tests/js/report-create.test.cjs](C:/Users/PC/Documents/lf_ai/lost-found/tests/js/report-create.test.cjs) â€” Six Node tests for double submissions, photo retention, validation, network errors and camera File handling.

## 4. Database Changes

Migration: `2026_10_14_000001_add_audit_workflow_fields.php`.

- `claims.rejection_reason`: nullable text; existing claims are preserved.
- `item_reports.reporter_name`: nullable string(190).
- `item_reports.submission_id`: nullable UUID; historical reports retain null identifiers.
- `reports_submission_unique`: unique composite index on `(reporter_user_id, submission_id)`.
- Rollback drops this index and these newly added fields. A regression test verifies that existing report rows survive rollback/reapplication. As with any dropped column, rollback discards values stored in the new fields.

The migration has NOT been applied to the configured application database. It must be applied in the normal release process before using these code changes. No historical counts or statuses were rewritten.

## 5. Test Results

| Executed check | Actual result |
| --- | --- |
| `php artisan test` | **109 tests passed; 382 assertions; 0 failures.** Includes existing registration, authorization, claim submission/approval/cancellation, pagination, export and account-role regressions plus 19 new issue tests. |
| `node --test tests/js/report-create.test.cjs` | **6 tests passed; 0 failures.** Covers overlapping clicks, retained camera File and UUID after 422, native validation, Found-name requirements, network failures and Camera Done byte preservation. |
| `python tests/Support/http-audit.py` against isolated server | **4 checks passed.** Shared cookie jar retains distinct identities; invalid/cross-boundary CSRF returns 419; eight overlapping HTTP retries produce one report (one 201 and seven 200 replays); user logout preserves admin identity. |
| Browser: claim rejection | Passed: whitespace rejected; valid reason saved; admin refresh retains it; claimant sees it; finalized action buttons disappear. |
| Browser: photos/contact override | Passed: selected image preview survives server validation; missing reporter name blocks Found override; corrected form submits the retained image. Physical webcam capture was not performed. |
| Browser: independent tabs | Passed: admin and user sign in within the same browser; admin refresh retains identity; user logout preserves admin; admin logout preserves user after refresh. |
| Browser: manual search | Passed: Found/text filtering returns the correct item detail row. Backend tests also exercise category, location, wildcard literals, empty results and permissions. |
| `npm run build` | Passed. Vite emitted its existing empty app-JavaScript chunk warning; public scripts were separately checked. |
| PHP `-l` on changed/new PHP files | Passed. |
| Node `--check` on changed/new JavaScript files | Passed. |
| `php artisan view:cache` | Passed. |
| `git diff --check` | Passed. |

No dedicated TypeScript/type-checking script exists in this PHP/Blade project. Syntax checks are not presented as static type analysis. Initial test-harness issues and the modal stacking issue were corrected before the final passing runs.

The PHPUnit suite uses in-memory SQLite. Real HTTP/browser tests used an explicitly separate disposable SQLite database, array mail transport and a local server. Test fixture records are not production records or fabricated production metrics.

## 6. Remaining Risks

- **Issue 2:** HTTP clients overlapped, but PHP's Windows development server processes requests serially. The unique-index safeguard is verified; genuinely simultaneous transactions and row locking must still be exercised using the production MySQL engine and multiple server workers. No production load-test result is claimed.
- **Issue 5:** Camera Done byte handling, retained File state, backend validation and browser image upload are verified. A physical webcam/phone capture followed by validation remains a manual device check. Camera access still depends on supported browsers, permissions and HTTPS/localhost.
- Session isolation supports one staff session and one regular session per browser profile. Multiple tabs within the same boundary continue to share that boundary's session; arbitrary independent same-role accounts were not implemented.
- Secure-cookie behavior over the production HTTPS host, long-lived remember-me expiry, actual SMTP delivery and external AI analysis were not exercised. Their existing implementations/configuration remain; mail and AI dispatch were isolated/faked in tests.
- Older offline drafts without an account/endpoint identifier are retained but are no longer automatically replayed under an unknown identity.
- The migration is release work still required on the intended database. This task did not deploy or change production schema/data.

## 7. Final Status

Statuses below describe verification in the available local environment and include the outstanding device/database checks explicitly.

| Issue | Status |
| --- | --- |
| 1. Admin rejection notes | **FIXED AND VERIFIED** |
| 2. Duplicate report submission | **IMPLEMENTED, PENDING VERIFICATION** â€” MySQL multi-worker transaction concurrency remains. |
| 3. Manual match search | **FIXED AND VERIFIED** |
| 4. Found override reporter name | **FIXED AND VERIFIED** |
| 5. Camera photo retention | **IMPLEMENTED, PENDING VERIFICATION** â€” physical device capture remains. |
| 6. Landing Cellphone label | **FIXED AND VERIFIED** |
| 7. Claimed item count | **FIXED AND VERIFIED** |
| 8. Independent admin/user sessions | **FIXED AND VERIFIED** |

Browser evidence from isolated test fixtures:

![Claimant sees the saved rejection reason after refresh](C:/Users/PC/Documents/lf_ai/lost-found/storage/framework/testing/claimant-rejection-proof.png)
