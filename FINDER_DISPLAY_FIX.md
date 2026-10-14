# Finder name display and dashboard label correction

October 14, 2026 — follow-up to the live Report #49 screenshot.

## Confirmed code defects

The report details view previously rendered a name only when an explicit `reporter_name` or student-surrender record existed. A student who submitted a Found report under their own account was not represented in that display. The previous new finder panel also depended on the surrender flag, hiding independently recorded finder details when that flag was false.

“Awaiting Review” was a display label substituted for the existing `claimed` report status. It was not a new database status. The client expects the original terminology; the chart now displays “Claimed” again. The successful approved-claim metric remains separately labeled “Claimed Items.” No statuses or counts were rewritten.

## Implemented behavior

- Staff Found-report details always show a Finder's Name section.
- Name precedence: recorded `finder_name`, explicit legacy `reporter_name`, then the profile/name of a regular submitting account.
- Explicitly recorded finder details display independently of the student-surrender flag.
- New regular-user Found reports snapshot the authenticated finder's name and school/student ID. Future profile edits do not rename that recorded finder.
- If an override names another person, the submitting account's student ID is not copied to that person's finder details.
- The authenticated admin who submitted another person's report is not guessed to be its finder. Missing information is displayed as “Not recorded,” with “Submitted By” shown separately.
- Finder identity remains staff-only. Normal owners and matched claimants do not receive these fields in the report page.
- The dashboard's original Claimed status label is restored. Approved Claim Items still count unique approved report IDs.

## Modified code

- `app/Models/ItemReport.php`: shared finder identity resolution.
- `app/Http/Controllers/ItemReportController.php`: authenticated finder snapshots and authorized reporter-profile loading.
- `resources/views/reports/show.blade.php`: clear Finder's Name / Student ID / Submitted By display and honest missing-data state.
- `resources/views/dashboard.blade.php`: restore the original Claimed label.
- `tests/Feature/FinderDisplayRegressionTest.php`: eight regressions for old/new reports, snapshots, overrides, privacy, missing data and dashboard terminology.

## Verification

- `php artisan test`: **128 passed, 480 assertions**.
- `node --test tests/js/*.test.cjs`: **10 passed**.
- `npm run build`, `php artisan view:cache`, changed PHP syntax checks and `git diff --check`: passed.
- No production record or database schema was modified. Live Report #49's saved values were not inspected; no live-data repair or live deployment is claimed.

## Release state

GitHub `main` was checked and still points to `2ad448311f10956c5a4bcfbae584bbd2badad786`. The newer student-finder/session implementation and this correction remain local, uncommitted and unpushed. The earlier local migration `2026_10_14_000002_add_student_finder_details.php` remains required when these changes are released; this correction adds no additional migration.

If an older admin-submitted report never recorded its actual finder, that identity cannot be recovered from the screenshot or guessed from the admin account. The true finder details must come from the original record/source.
