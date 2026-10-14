<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemReportRequest;
use App\Jobs\ProcessImageAnalysis;
use App\Models\Category;
use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\Location;
use App\Models\ReportMatch;
use App\Models\ReportPhoto;
use App\Models\User;
use App\Services\ClaimEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItemReportController extends WebBaseController
{
    /* =========================
     * CONFIG
     * ========================= */
    private const MATCH_CANDIDATE_LIMIT = 20;
    private const MATCH_UPSERT_THRESHOLD = 35.0;   // create/update ReportMatch rows
    private const MATCH_STATUS_THRESHOLD = 45.0;   // set report.status='matched'
    private const MATCH_MAX_PER_SHOW = 50;

    private const LOCKED_STATUSES = ClaimEligibilityService::LOCKED_STATUSES; // cannot edit reports in these states
    private const ACTIVE_STATUSES = ['pending', 'matched', 'claimed']; // used for matching candidates

    /* =========================
     * LIST
     * ========================= */
    public function index(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $isStaff = $this->hasAnyRole(['admin', 'osa']);

        $type       = (string) $request->query('type', '');
        $status     = (string) $request->query('status', '');
        $categoryId = (string) $request->query('category_id', '');
        $locationId = (string) $request->query('location_id', '');
        $q          = trim((string) $request->query('q', ''));
        $dateFrom   = trim((string) $request->query('date_from', ''));
        $dateTo     = trim((string) $request->query('date_to', ''));

        $query = ItemReport::with(['photos', 'category', 'location']);

        if ($type !== '') $query->where('report_type', $type);
        if ($status !== '') $query->where('status', $status);
        if ($categoryId !== '') $query->where('category_id', (int) $categoryId);
        if ($locationId !== '') $query->where('location_id', (int) $locationId);

        if (!$isStaff) {
            $query->where('reporter_user_id', (int) $this->user()->id);
        }

        // Date range filter — filter by incident_date (if set) else created_at
        // Fixed: previously ignored date_from/date_to so July reports still showed when filtering Aug-Sep
        if ($dateFrom !== '' && strtotime($dateFrom) !== false) {
            $query->where(function ($qq) use ($dateFrom) {
                $qq->whereDate('incident_date', '>=', $dateFrom)
                   ->orWhere(function ($q2) use ($dateFrom) {
                       $q2->whereNull('incident_date')->whereDate('created_at', '>=', $dateFrom);
                   });
            });
        }
        if ($dateTo !== '' && strtotime($dateTo) !== false) {
            $query->where(function ($qq) use ($dateTo) {
                $qq->whereDate('incident_date', '<=', $dateTo)
                   ->orWhere(function ($q2) use ($dateTo) {
                       $q2->whereNull('incident_date')->whereDate('created_at', '<=', $dateTo);
                   });
            });
        }

        if ($q !== '') {
            $matchExpr = "MATCH(item_name,item_description,brand_model,color,circumstances) AGAINST (? IN NATURAL LANGUAGE MODE)";
            $query->select('item_reports.*')
                ->selectRaw("$matchExpr AS relevance", [$q])
                ->whereRaw($matchExpr, [$q])
                ->orderByDesc('relevance')
                ->orderByDesc('id');
        } else {
            $query->orderByDesc('id');
        }

        $reports = $query->paginate(20)->withQueryString();

        $categories = Cache::remember('categories_list', 3600, fn() =>
            Category::orderBy('name')->get()
        );
        $locations  = Cache::remember('locations_list', 3600, fn() =>
            Location::orderBy('name')->get()
        );

        return view('reports.index', compact(
            'reports', 'type', 'status', 'categoryId', 'locationId', 'q', 'categories', 'locations', 'isStaff', 'dateFrom', 'dateTo'
        ));
    }

    /* =========================
     * CREATE
     * ========================= */
    public function create(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $categories = Cache::remember('categories_list', 3600, fn() =>
            Category::orderBy('name')->get()
        );
        $locations  = Cache::remember('locations_list', 3600, fn() =>
            Location::orderBy('name')->get()
        );

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        return view('reports.create', compact('categories', 'locations', 'isStaff'));
    }

    public function store(ItemReportRequest $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $data = $request->validated();

        $reporterId = (int) $this->user()->id;
        $isStaff    = $this->hasAnyRole(['admin', 'osa']);

        if (!$isStaff && $data['report_type'] === 'found') {
            // Snapshot the authenticated finder so future profile edits don't rename old reports.
            $profileName = trim((string) ($this->user()->profile?->full_name ?: $this->user()->name));
            $overrideName = trim((string) ($data['reporter_name'] ?? ''));
            $data['finder_name'] = $overrideName !== '' ? $overrideName : ($profileName !== '' ? $profileName : null);
            $data['finder_student_id'] = $overrideName === '' || mb_strtolower($overrideName) === mb_strtolower($profileName)
                ? $this->user()->profile?->school_id_number : null;
        }

        $reportId = null;
        $created = false;
        $storedPhotos = [];

        try {
            DB::transaction(function () use ($request, $data, $reporterId, $isStaff, &$reportId, &$created, &$storedPhotos) {
                // Serialize submissions by this account, including concurrent retries.
                User::whereKey($reporterId)->lockForUpdate()->firstOrFail();
                $existing = ItemReport::where('reporter_user_id', $reporterId)
                    ->where('submission_id', $data['submission_id'])->first();
                if ($existing) {
                    $reportId = (int) $existing->id;
                    return;
                }
                $report = ItemReport::create([
                    'submission_id'     => $data['submission_id'],
                    'reporter_name'     => $data['reporter_name'] ?? null,
                    'surrendered_by_student' => $isStaff && !empty($data['surrendered_by_student']),
                    'finder_name'       => $data['finder_name'] ?? null,
                    'finder_student_id' => $data['finder_student_id'] ?? null,
                    'report_type'       => $data['report_type'],
                    'reporter_user_id'  => $reporterId,
                    // Owner is usually unknown; only staff should set it.
                    'owner_user_id'     => $isStaff ? ($data['owner_user_id'] ?? null) : null,
                    'category_id'       => $data['category_id'] ?? null,
                    'item_name'         => $data['item_name'] ?? null,
                    'item_description'  => $data['item_description'],
                    'brand_model'       => $data['brand_model'] ?? null,
                    'color'             => $data['color'] ?? null,
                    'incident_date'     => $data['incident_date'] ?? null,
                    'incident_time'     => $data['incident_time'] ?? null,
                    'location_id'       => $data['location_id'] ?? null,
                    'circumstances'     => $data['circumstances'] ?? null,
                    'contact_override'  => $data['contact_override'] ?? null,
                    'status'            => 'pending',
                    'locked_at'         => now(),
                    'locked_by_user_id' => null,
                    'matched_report_id' => null,
                    'matched_score'     => null,
                ]);

                $reportId = (int) $report->id;
                $created = true;

                if (!empty($data['photos'] ?? [])) {
                    foreach ($data['photos'] as $file) {
                        $storedPhotos[] = $this->storeReportPhoto($report->id, $file);
                    }
                }

                $this->audit($request, 'reports.create', 'item_reports', $report->id, [
                    'type'   => $report->report_type,
                    'status' => $report->status,
                ]);

                // AUTO matching may update status to 'matched'
                $this->runAutoMatch($request, $report->fresh());
                User::where('is_active', 1)->whereKeyNot($reporterId)
                    ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'osa']))
                    ->each(function ($staff) use ($report) {
                        $this->notify((int) $staff->id, 'report', 'New report submitted',
                            ucfirst($report->report_type) . ' report #' . $report->id . ' was submitted.',
                            ['report_id' => $report->id]);
                    });
            });
        } catch (\Throwable $exception) {
            foreach ($storedPhotos as $url) $this->tryDeletePublicUrlFile($url);
            throw $exception;
        }

        // Trigger AI Analysis after transaction commits
        if ($created && !empty($data['photos'] ?? [])) {
            try {
                ProcessImageAnalysis::dispatch((int) $reportId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('AI analysis dispatch failed: ' . $e->getMessage());
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('reports.show', $reportId)], $created ? 201 : 200);
        }
        return redirect()->route('reports.show', $reportId)->with('success', 'Created');
    }

    /* =========================
     * SHOW
     * ========================= */
    public function show(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::with(['photos', 'category', 'location'])->findOrFail($id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;
        $eligibilityService = app(ClaimEligibilityService::class);

        // The Lost reporter must be able to inspect the FOUND report matched to
        // their own Lost report in order to claim it. Matching grants read access
        // only - $isOwner (edit rights) is unchanged.
        $canView = $isStaff
            || $isOwner
            || $eligibilityService->canViewFoundReport($report, $this->user());

        if (!$canView) abort(403);

        if ($isStaff) {
            $report->loadMissing('reporter.profile');
        }

        $claimEligibility = $eligibilityService->evaluate($report, $this->user());

        // Match internals (counterpart report, score, match method) are staff-only.
        // A student gets no Matches card at all - they get a plain "Submit Claim"
        // action instead when one of their lost reports is matched to a claimable
        // found report, so the claim workflow survives without leaking the matching.
        $matches = $isStaff
            ? ReportMatch::query()
                ->where(function ($query) use ($id) {
                    $query->where('lost_report_id', $id)->orWhere('found_report_id', $id);
                })
                ->orderByDesc('score')
                ->limit(self::MATCH_MAX_PER_SHOW)
                ->get()
            : collect();

        $claimableFoundIds = $eligibilityService->claimableFoundReportIds($report, $this->user(), $isStaff);

        $claims = Claim::with('claimant')
            ->where('report_id', $id)
            ->orderByDesc('id')
            ->get();

        // convenience for blade (so you don’t compute again)
        $isLocked = $report->isLocked();
        $canEdit = !$isLocked && ($isStaff || $isOwner);
        $report->setAttribute('can_edit', $canEdit);
        $report->setAttribute('is_locked', $isLocked);
        $lockerName = null;
        if ($report->locked_by_user_id) {
            $locker = \App\Models\User::with('profile')->find((int) $report->locked_by_user_id);
            $lockerName = $locker?->profile?->full_name ?: $locker?->email;
        }
        $statusColor = match ((string) $report->status) {
            'pending'  => 'secondary',
            'matched'  => 'primary',
            'claimed'  => 'warning',
            'returned' => 'success',
            'archived' => 'dark',
            default    => 'secondary',
        };

        return view('reports.show', compact('report', 'matches', 'claims', 'isStaff', 'isOwner', 'statusColor', 'claimEligibility', 'claimableFoundIds', 'isLocked', 'lockerName'));
    }

    /* =========================
     * EDIT
     * ========================= */
    public function edit(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::with('photos')->findOrFail($id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if ($report->isLocked()) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'This report is locked and cannot be edited.']);
        }

        $categories = Cache::remember('categories_list', 3600, fn() =>
            Category::orderBy('name')->get()
        );
        $locations  = Cache::remember('locations_list', 3600, fn() =>
            Location::orderBy('name')->get()
        );
        $users = User::with('profile')->orderBy('email')->get();

        return view('reports.edit', compact('report', 'categories', 'locations', 'isStaff', 'isOwner', 'users'));
    }

    public function update(ItemReportRequest $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::findOrFail($id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if ($report->isLocked()) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'This report is locked and cannot be edited.']);
        }

        $data = $request->validated();

        // Staff-only fields
        if ($isStaff) {
            $rules = [
                'status' => ['required', \Illuminate\Validation\Rule::in(array_merge(self::ACTIVE_STATUSES, self::LOCKED_STATUSES))],
            ];
            $request->validate($rules);
        }

        DB::transaction(function () use ($request, $report, $data, $isStaff) {
            $beforeStatus = $report->status;

            $payload = [
                'category_id'      => $data['category_id'] ?? null,
                'item_name'        => $data['item_name'] ?? null,
                'item_description' => $data['item_description'],
                'brand_model'      => $data['brand_model'] ?? null,
                'color'            => $data['color'] ?? null,
                'incident_date'    => $data['incident_date'] ?? null,
                'incident_time'    => $data['incident_time'] ?? null,
                'location_id'      => $data['location_id'] ?? null,
                'circumstances'    => $data['circumstances'] ?? null,
                'contact_override' => $data['contact_override'] ?? null,
                'reporter_name' => $data['reporter_name'] ?? null,
            ];

            if ($isStaff) {
                $payload['surrendered_by_student'] = !empty($data['surrendered_by_student']);
                $payload['finder_name'] = $data['finder_name'] ?? null;
                $payload['finder_student_id'] = $data['finder_student_id'] ?? null;
                $payload['owner_user_id'] = $data['owner_user_id'] ?? null;
                if (isset($data['status'])) {
                    $payload['status'] = $data['status'];
                }
            }

            if (!$isStaff && $report->report_type === 'found') {
                $payload['finder_name'] = $data['reporter_name'];
                if ($data['reporter_name'] !== $report->finderIdentity()['name']) {
                    $payload['finder_student_id'] = null;
                }
            }

            // IMPORTANT: status is NOT user-editable here
            $report->update($payload);

            $this->audit($request, 'reports.update', 'item_reports', $report->id);

            // Re-run matching after updates (auto may set status->matched)
            $this->runAutoMatch($request, $report->fresh());

            $afterStatus = (string) $report->fresh()->status;
            if ($afterStatus !== $beforeStatus) {
                $reason = ($isStaff && isset($data['status']) && $data['status'] !== $beforeStatus)
                    ? 'Manual status change by admin'
                    : 'Auto status change via matching';

                $this->pushReportStatus(
                    $report->id,
                    $beforeStatus,
                    $afterStatus,
                    (int) $this->user()->id,
                    $reason
                );
            }
        });

        return redirect()->route('reports.show', $id)->with('success', 'Updated');
    }

    /* =========================
     * PHOTOS
     * ========================= */

    public function storePhoto(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::with('photos')->findOrFail($id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if ($report->isLocked()) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'This report is locked and cannot be edited.']);
        }

        $data = $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $report, $data) {
            $this->storeReportPhoto($report->id, $data['photo']);
            $this->audit($request, 'reports.photo.upload', 'item_reports', $report->id);

            // Trigger AI Analysis for new photo (async via queue)
            try {
                ProcessImageAnalysis::dispatch((int) $report->id);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('AI analysis dispatch failed: ' . $e->getMessage());
            }
        });

        return redirect()->route('reports.edit', $id)->with('success', 'Photo uploaded');
    }

    public function destroyPhoto(Request $request, int $photoId)
    {
        if (!$this->user()) return redirect()->route('login');

        $photo = ReportPhoto::findOrFail($photoId);
        $report = ItemReport::findOrFail((int) $photo->report_id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if ($report->isLocked()) {
            return redirect()->route('reports.show', $report->id)->withErrors(['message' => 'This report is locked and cannot be edited.']);
        }

        DB::transaction(function () use ($request, $photo, $report) {
            // try to delete file if it is stored under public disk
            $this->tryDeletePublicUrlFile($photo->photo_url);

            $photo->delete();

            $this->audit($request, 'reports.photo.delete', 'report_photos', $photo->id, [
                'report_id' => $report->id,
            ]);
        });

        return redirect()->route('reports.edit', $report->id)->with('success', 'Photo deleted');
    }

    /* =========================
     * STATUS (AUTO + STAFF ONLY ACTIONS)
     * =========================
     * - matched: AUTO by runAutoMatch
     * - claimed: should be set by ClaimController when a claim is approved
     * - returned/archived: STAFF ONLY
     */

    public function markReturned(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::findOrFail($id);

        if (!$this->hasAnyRole(['admin','osa'])) abort(403);

        // Locking protects the report details/photos from edits, not the
        // workflow status — staff must be able to mark a claimed item returned.
        if (!in_array($report->status, ['claimed'], true)) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'Only claimed reports can be marked as returned']);
        }

        DB::transaction(function () use ($request, $report) {
            $oldStatus = $report->status;
            $newStatus = 'returned';

            $report->update(['status' => $newStatus]);

            $this->pushReportStatus($report->id, $oldStatus, $newStatus, (int) $this->user()->id, 'Marked as returned');
            $this->audit($request, 'reports.returned', 'item_reports', $report->id);

            $this->notify((int) $report->reporter_user_id, 'report_status', 'Report Status Updated', "Your report #{$report->id} changed from {$oldStatus} to {$newStatus}.");
        });

        return redirect()->route('reports.show', $report->id)->with('success', 'Report marked as returned');
    }

    public function archive(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        if (!$this->hasAnyRole(['admin', 'osa'])) abort(403);

        $report = ItemReport::findOrFail($id);

        // Locking protects the report details from edits; archiving is a staff
        // workflow transition and must still work on submitted (locked) reports.
        if (!in_array($report->status, ['returned', 'claimed'], true)) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'Only returned/claimed reports can be archived']);
        }

        DB::transaction(function () use ($request, $report) {
            $old = $report->status;
            $report->update(['status' => 'archived']);

            $this->pushReportStatus($report->id, $old, 'archived', (int) $this->user()->id, 'Staff archived report');
            $this->audit($request, 'reports.archived', 'item_reports', $report->id);

            $this->notify((int) $report->reporter_user_id, 'status', 'Report updated', 'Your report has been archived.', [
                'report_id' => $report->id,
                'status' => 'archived',
            ]);
        });

        return redirect()->route('reports.show', $id)->with('success', 'Archived');
    }

    /* =========================
     * LOCK / UNLOCK (staff only)
     * =========================
     * A locked report cannot be edited by anyone — details and photos are
     * frozen. Staff workflow statuses (claimed -> returned -> archived) are
     * still advanced through the dedicated action buttons.
     */
    public function setLocked(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $this->requireAnyRole(['admin', 'osa']);

        $report = ItemReport::findOrFail($id);

        $data = $request->validate([
            'locked' => ['required', 'boolean'],
        ]);

        $locked = (bool) $data['locked'];

        // A submitted report stays locked for its security and audit trail.
        // Staff may lock legacy/unlocked reports, but cannot reopen one.
        if (!$locked && $report->locked_at !== null) {
            return redirect()->route('reports.show', $id)->withErrors([
                'message' => 'Submitted reports cannot be unlocked for editing.',
            ]);
        }

        $report->update([
            'locked_at' => $locked ? now() : null,
            'locked_by_user_id' => $locked ? (int) $this->user()->id : null,
        ]);

        $this->audit($request, $locked ? 'reports.lock' : 'reports.unlock', 'item_reports', $report->id);

        if (!$locked) {
            $this->notify(
                (int) $report->reporter_user_id,
                'report_status',
                'Report unlocked',
                "Report #{$report->id} can be edited again."
            );
        }

        return redirect()->route('reports.show', $id)->with(
            'success',
            $locked
                ? 'Report locked — it can no longer be edited.'
                : 'Report unlocked — editing is allowed again.'
        );
    }

    /**
     * Backward-compatible endpoint if you already have routes to reports.status
     * - Normal users: allowed ONLY to archive their own report (optional)
     * - Staff: should NOT use this; keep UI on buttons
     */
    public function setStatus(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::findOrFail($id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);

        // Only allow owner -> archived (optional safety)
        $request->validate([
            'status' => ['required', Rule::in(['archived'])],
        ]);

        if ($report->isLocked()) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'This report is locked and cannot be edited.']);
        }

        // Staff should NOT manually set random statuses here
        if ($isStaff) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'Use staff action buttons for status changes']);
        }

        $old = $report->status;

        DB::transaction(function () use ($request, $report, $old) {
            $report->update(['status' => 'archived']);

            $this->pushReportStatus($report->id, $old, 'archived', (int) $this->user()->id, 'User archived');
            $this->audit($request, 'reports.archived.user', 'item_reports', $report->id, ['from' => $old]);
        });

        return redirect()->route('reports.show', $id)->with('success', 'Archived');
    }

    /* =========================
     * AUTO MATCHING (STATUS: matched)
     * ========================= */
    private function runAutoMatch(Request $request, ItemReport $report): void
    {
        // Never auto-match locked reports
        if ($this->isLocked($report->status)) return;

        $text = $this->buildMatchText($report);
        if ($text === '') return;

        $oppositeType = $report->report_type === 'lost' ? 'found' : 'lost';
        $matchExpr    = "MATCH(item_name,item_description,brand_model,color,circumstances) AGAINST (? IN NATURAL LANGUAGE MODE)";

        try {
            $candidates = ItemReport::query()
                ->select('item_reports.*')
                ->selectRaw("$matchExpr AS relevance", [$text])
                ->where('report_type', $oppositeType)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->where('id', '<>', $report->id)
                ->whereRaw($matchExpr, [$text])
                ->orderByDesc('relevance')
                ->orderByDesc('id')
                ->limit(self::MATCH_CANDIDATE_LIMIT)
                ->get();
        } catch (\Exception $e) {
            // FULLTEXT index may not exist yet — fall back to simple LIKE search
            \Illuminate\Support\Facades\Log::warning('FULLTEXT match failed, falling back to LIKE: ' . $e->getMessage());

            $keywords = array_filter(explode(' ', $text), fn($w) => mb_strlen($w) > 2);
            if (empty($keywords)) return;

            $escapedKeywords = array_map(fn($kw) => str_replace(['%', '_'], ['\\%', '\\_'], $kw), $keywords);

            $candidates = ItemReport::query()
                ->select('item_reports.*')
                ->where('report_type', $oppositeType)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->where('id', '<>', $report->id)
                ->where(function ($q) use ($escapedKeywords) {
                    foreach ($escapedKeywords as $kw) {
                        $q->orWhere('item_name', 'LIKE', "%{$kw}%")
                          ->orWhere('item_description', 'LIKE', "%{$kw}%")
                          ->orWhere('brand_model', 'LIKE', "%{$kw}%")
                          ->orWhere('color', 'LIKE', "%{$kw}%");
                    }
                })
                ->limit(self::MATCH_CANDIDATE_LIMIT)
                ->get();

            // Add synthetic relevance score for fallback
            foreach ($candidates as $c) {
                $c->relevance = 1.0;
            }
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($candidates as $c) {
            $score = $this->scoreCandidate($report, $c, (float) $c->relevance);

            // create/update match rows
            if ($score >= self::MATCH_UPSERT_THRESHOLD) {
                $this->upsertMatch($request, $report, $c, $score, 'keyword');
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $c;
            }
        }

        // If no good best candidate
        if (!$best || $bestScore < self::MATCH_STATUS_THRESHOLD) return;

        DB::transaction(function () use ($request, $report, $best, $bestScore) {
            // Re-fetch to avoid stale status
            $r = ItemReport::findOrFail($report->id);
            $b = ItemReport::findOrFail($best->id);

            if ($this->isLocked($r->status) || $this->isLocked($b->status)) return;

            // Only auto-set matched if current is pending/matched (do NOT override claimed)
            $canSetMatchedR = in_array($r->status, ['pending', 'matched'], true);
            $canSetMatchedB = in_array($b->status, ['pending', 'matched'], true);

            // Update report side if this best score is better than existing
            $shouldUpdateR = $canSetMatchedR && (
                $r->matched_report_id === null ||
                (float) ($r->matched_score ?? 0) < (float) $bestScore
            );

            if ($shouldUpdateR) {
                $old = $r->status;

                $r->update([
                    'matched_report_id' => $b->id,
                    'matched_score'     => $bestScore,
                    'status'            => 'matched',
                ]);

                if ($old !== 'matched') {
                    $this->pushReportStatus($r->id, $old, 'matched', (int) $this->user()->id, 'Auto matched (suggested)');
                }

                $this->notify((int) $r->reporter_user_id, 'match', 'Match suggested', 'A possible match was found for your report.', [
                    'report_id' => $r->id,
                    'matched_report_id' => $b->id,
                    'score' => $bestScore,
                ]);
            }

            // Optional: Update candidate side symmetrically (best-effort)
            $shouldUpdateB = $canSetMatchedB && (
                $b->matched_report_id === null ||
                (float) ($b->matched_score ?? 0) < (float) $bestScore
            );

            if ($shouldUpdateB) {
                $oldB = $b->status;

                $b->update([
                    'matched_report_id' => $r->id,
                    'matched_score'     => $bestScore,
                    'status'            => 'matched',
                ]);

                if ($oldB !== 'matched') {
                    $this->pushReportStatus($b->id, $oldB, 'matched', (int) $this->user()->id, 'Auto matched (suggested)');
                }

                $this->notify((int) $b->reporter_user_id, 'match', 'Match suggested', 'A possible match was found for your report.', [
                    'report_id' => $b->id,
                    'matched_report_id' => $r->id,
                    'score' => $bestScore,
                ]);
            }

            $this->audit($request, 'reports.auto_match', 'item_reports', $r->id, [
                'report_id' => $r->id,
                'best_candidate_id' => $b->id,
                'score' => $bestScore,
            ]);
        });
    }

    private function buildMatchText(ItemReport $report): string
    {
        $parts = array_filter([
            (string) $report->item_name,
            (string) $report->brand_model,
            (string) $report->color,
            (string) $report->item_description,
            (string) $report->circumstances,
        ], fn($v) => trim((string) $v) !== '');

        return trim(implode(' ', $parts));
    }

    private function scoreCandidate(ItemReport $a, ItemReport $b, float $relevance): float
    {
        $score = 0.0;

        // 1. Item Name similarity (max 30 pts)
        $nameA = trim(mb_strtolower((string) $a->item_name));
        $nameB = trim(mb_strtolower((string) $b->item_name));
        if ($nameA !== '' && $nameB !== '') {
            if ($nameA === $nameB) {
                $score += 30.0;
            } else {
                // Check if one contains the other
                if (str_contains($nameA, $nameB) || str_contains($nameB, $nameA)) {
                    $score += 22.0;
                } else {
                    // Word overlap scoring
                    $wordsA = array_filter(explode(' ', $nameA));
                    $wordsB = array_filter(explode(' ', $nameB));
                    $common = array_intersect($wordsA, $wordsB);
                    $totalWords = max(count($wordsA), count($wordsB));
                    if ($totalWords > 0) {
                        $score += (count($common) / $totalWords) * 25.0;
                    }
                }
            }
        }

        // 2. Color match (max 20 pts)
        $colorA = trim(mb_strtolower((string) $a->color));
        $colorB = trim(mb_strtolower((string) $b->color));
        if ($colorA !== '' && $colorB !== '') {
            if ($colorA === $colorB) {
                $score += 20.0;
            } elseif (str_contains($colorA, $colorB) || str_contains($colorB, $colorA)) {
                $score += 12.0;
            }
        }

        // 3. Location match (max 20 pts)
        if ($a->location_id && $b->location_id && (int) $a->location_id === (int) $b->location_id) {
            $score += 20.0;
        }

        // 4. Category match (max 10 pts)
        if ($a->category_id && $b->category_id && (int) $a->category_id === (int) $b->category_id) {
            $score += 10.0;
        }

        // 5. Brand/Model match (max 10 pts)
        $bmA = trim(mb_strtolower((string) $a->brand_model));
        $bmB = trim(mb_strtolower((string) $b->brand_model));
        if ($bmA !== '' && $bmB !== '') {
            if ($bmA === $bmB) {
                $score += 10.0;
            } elseif (str_contains($bmA, $bmB) || str_contains($bmB, $bmA)) {
                $score += 6.0;
            }
        }

        // 6. Date proximity bonus (max 10 pts) - closer dates = higher score
        if ($a->incident_date && $b->incident_date) {
            try {
                $da = new \DateTime((string) $a->incident_date);
                $db = new \DateTime((string) $b->incident_date);
                $diffDays = (int) $da->diff($db)->format('%a');
                if ($diffDays <= 7) {
                    $score += max(0.0, 10.0 - ($diffDays * 1.4));
                }
            } catch (\Throwable $e) {}
        }

        return round(min(100.0, $score), 2);
    }

    private function upsertMatch(Request $request, ItemReport $report, ItemReport $candidate, float $score, string $method): void
    {
        $lostId  = $report->report_type === 'lost' ? $report->id : $candidate->id;
        $foundId = $report->report_type === 'found' ? $report->id : $candidate->id;

        $existing = ReportMatch::where('lost_report_id', $lostId)
            ->where('found_report_id', $foundId)
            ->first();

        if ($existing) {
            // update only if it improves AND still suggested
            if ($existing->status === 'suggested' && $score > (float) $existing->score) {
                $existing->update([
                    'score'  => $score,
                    'method' => $method,
                ]);
            }
            return;
        }

        ReportMatch::create([
            'lost_report_id'  => $lostId,
            'found_report_id' => $foundId,
            'score'           => $score,
            'method'          => $method,
            'status'          => 'suggested',
        ]);

        $this->audit($request, 'matches.suggested', 'report_matches', null, [
            'lost_report_id'  => $lostId,
            'found_report_id' => $foundId,
            'score'           => $score,
            'method'          => $method,
        ]);
    }

    /* =========================
     * HELPERS
     * ========================= */
    private function isLocked(string $status): bool
    {
        return in_array($status, self::LOCKED_STATUSES, true);
    }

    private function storeReportPhoto(int $reportId, $file): string
    {
        $images = app(\App\Services\UploadedImageStorage::class);
        $temporary = tempnam(sys_get_temp_dir(), 'lf_image_');
        if ($temporary === false) throw new \RuntimeException('Cannot prepare image upload.');
        try {
            $compressed = false;
            try {
                $this->compressImage($file->getPathname(), $temporary, 82);
                $compressed = true;
            } catch (\Throwable $exception) {
                // Preserve the original image when compression is unavailable.
            }
            $url = $compressed
                ? $images->put('report_photos/' . (string) \Illuminate\Support\Str::uuid() . '.jpg', file_get_contents($temporary))
                : $images->store($file, 'report_photos');
        } finally {
            if (is_file($temporary)) unlink($temporary);
        }

        try {
            ReportPhoto::create([
                'report_id'  => $reportId,
                'photo_url'  => $url,
                'caption'    => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $this->tryDeletePublicUrlFile($url);
            throw $exception;
        }
        return $url;
    }

    private function compressImage(string $source, string $destination, int $quality = 82): void
    {
        $imageInfo = getimagesize($source);
        if (!$imageInfo) {
            throw new \RuntimeException('Cannot read image');
        }

        $mime = $imageInfo['mime'];
        $src = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($source),
            'image/png' => imagecreatefrompng($source),
            'image/webp' => imagecreatefromwebp($source),
            'image/gif' => imagecreatefromgif($source),
            default => throw new \RuntimeException('Unsupported image type: ' . $mime),
        };

        if (!$src) {
            throw new \RuntimeException('Failed to create image resource');
        }

        // Resize if larger than 1200px
        $maxWidth = 1200;
        $maxHeight = 1200;
        [$origWidth, $origHeight] = [$imageInfo[0], $imageInfo[1]];

        if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
            $newWidth = (int) ($origWidth * $ratio);
            $newHeight = (int) ($origHeight * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($src);
            $src = $resized;
        }

        imagejpeg($src, $destination, $quality);
        imagedestroy($src);
    }

    private function tryDeletePublicUrlFile(?string $url): void
    {
        app(\App\Services\UploadedImageStorage::class)->delete($url);
    }
}
