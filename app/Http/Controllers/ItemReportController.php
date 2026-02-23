<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImageAnalysis;
use App\Models\Category;
use App\Models\ItemReport;
use App\Models\Location;
use App\Models\ReportMatch;
use App\Models\ReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    private const LOCKED_STATUSES = ['returned', 'archived']; // cannot edit reports in these states
    private const ACTIVE_STATUSES = ['pending', 'matched', 'claimed']; // used for matching candidates

    /* =========================
     * LIST
     * ========================= */
    public function index(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $type       = (string) $request->query('type', '');
        $status     = (string) $request->query('status', '');
        $categoryId = (string) $request->query('category_id', '');
        $locationId = (string) $request->query('location_id', '');
        $q          = trim((string) $request->query('q', ''));

        $query = ItemReport::query();

        if ($type !== '') $query->where('report_type', $type);
        if ($status !== '') $query->where('status', $status);
        if ($categoryId !== '') $query->where('category_id', (int) $categoryId);
        if ($locationId !== '') $query->where('location_id', (int) $locationId);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        if (!$isStaff) {
            $query->where('reporter_user_id', (int) $this->user()->id);
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

        $categories = Category::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();

        return view('reports.index', compact(
            'reports', 'type', 'status', 'categoryId', 'locationId', 'q', 'categories', 'locations', 'isStaff'
        ));
    }

    /* =========================
     * CREATE
     * ========================= */
    public function create(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $categories = Category::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();

        return view('reports.create', compact('categories', 'locations'));
    }

    public function store(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $data = $request->validate([
            'report_type'      => ['required', Rule::in(['lost', 'found'])],
            'owner_user_id'    => ['nullable', 'integer', 'exists:users,id'], // staff can set; for normal users it’s harmless if you later ignore
            'category_id'      => ['nullable', 'integer', 'exists:categories,id'],
            'item_name'        => ['nullable', 'string', 'max:190'],
            'item_description' => ['required', 'string', 'min:10'],
            'brand_model'      => ['nullable', 'string', 'max:190'],
            'color'            => ['nullable', 'string', 'max:60'],
            'incident_date'    => ['nullable', 'date'],
            'incident_time'    => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'location_id'      => ['nullable', 'integer', 'exists:locations,id'],
            'circumstances'    => ['nullable', 'string'],
            'contact_override' => ['nullable', 'string', 'max:255'],
            'photos'           => ['nullable', 'array'],
            'photos.*'         => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $reporterId = (int) $this->user()->id;
        $isStaff    = $this->hasAnyRole(['admin', 'osa']);

        $reportId = null;

        DB::transaction(function () use ($request, $data, $reporterId, $isStaff, &$reportId) {
            $report = ItemReport::create([
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
                'matched_report_id' => null,
                'matched_score'     => null,
            ]);

            $reportId = (int) $report->id;

            if (!empty($data['photos'] ?? [])) {
                foreach ($data['photos'] as $file) {
                    $this->storeReportPhoto($report->id, $file);
                }
                
                // Trigger AI Analysis
                ProcessImageAnalysis::dispatch((int) $report->id);
            }

            $this->audit($request, 'reports.create', 'item_reports', $report->id, [
                'type'   => $report->report_type,
                'status' => $report->status,
            ]);

            // AUTO matching may update status to 'matched'
            $this->runAutoMatch($request, $report->fresh());
        });

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

        if (!$isStaff && !$isOwner) abort(403);

        $matches = ReportMatch::query()
            ->where('lost_report_id', $id)
            ->orWhere('found_report_id', $id)
            ->orderByDesc('score')
            ->limit(self::MATCH_MAX_PER_SHOW)
            ->get();

        $claims = DB::table('claims')
            ->where('report_id', $id)
            ->orderByDesc('id')
            ->get();

        // convenience for blade (so you don’t compute again)
        $canEdit = !$this->isLocked($report->status) && ($isStaff || $isOwner);
        $report->can_edit = $canEdit; // dynamic property for views

        return view('reports.show', compact('report', 'matches', 'claims', 'isStaff', 'isOwner'));
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
        if ($this->isLocked($report->status)) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'Report is locked']);
        }

        $categories = Category::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();

        return view('reports.edit', compact('report', 'categories', 'locations', 'isStaff', 'isOwner'));
    }

    public function update(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::findOrFail($id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if ($this->isLocked($report->status)) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'Report is locked']);
        }

        $rules = [
            // owner_user_id should be staff-only; accept input but ignore if not staff
            'owner_user_id'    => ['nullable', 'integer', 'exists:users,id'],
            'category_id'      => ['nullable', 'integer', 'exists:categories,id'],
            'item_name'        => ['nullable', 'string', 'max:190'],
            'item_description' => ['required', 'string', 'min:10'],
            'brand_model'      => ['nullable', 'string', 'max:190'],
            'color'            => ['nullable', 'string', 'max:60'],
            'incident_date'    => ['nullable', 'date'],
            'incident_time'    => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'location_id'      => ['nullable', 'integer', 'exists:locations,id'],
            'circumstances'    => ['nullable', 'string'],
            'contact_override' => ['nullable', 'string', 'max:255'],
        ];

        if ($isStaff) {
            $rules['status'] = ['required', Rule::in(array_merge(self::ACTIVE_STATUSES, self::LOCKED_STATUSES))];
        }

        $data = $request->validate($rules);

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
            ];

            if ($isStaff) {
                $payload['owner_user_id'] = $data['owner_user_id'] ?? null;
                if (isset($data['status'])) {
                    $payload['status'] = $data['status'];
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
        if ($this->isLocked($report->status)) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'Report is locked']);
        }

        $data = $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $report, $data) {
            $this->storeReportPhoto($report->id, $data['photo']);
            $this->audit($request, 'reports.photo.upload', 'item_reports', $report->id);
            
            // Trigger AI Analysis for new photo
            ProcessImageAnalysis::dispatch((int) $report->id);
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
        if ($this->isLocked($report->status)) {
            return redirect()->route('reports.show', $report->id)->withErrors(['message' => 'Report is locked']);
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

        // allow archive only for returned or claimed (common workflow)
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
        $data = $request->validate([
            'status' => ['required', Rule::in(['archived'])],
            'note'   => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->isLocked($report->status)) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'Report is locked']);
        }

        // Staff should NOT manually set random statuses here
        if ($isStaff) {
            return redirect()->route('reports.show', $id)->withErrors(['message' => 'Use staff action buttons for status changes']);
        }

        $old = $report->status;

        DB::transaction(function () use ($request, $report, $data, $old) {
            $report->update(['status' => 'archived']);

            $this->pushReportStatus($report->id, $old, 'archived', (int) $this->user()->id, $data['note'] ?? 'User archived');
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
        // Relevance is usually small; scale to 0..60 (cap)
        $base = min(60.0, max(0.0, $relevance) * 10.0);
        $bonus = 0.0;

        // Category + Location + Color
        if ($a->category_id && $b->category_id && (int) $a->category_id === (int) $b->category_id) $bonus += 12.0;
        if ($a->location_id && $b->location_id && (int) $a->location_id === (int) $b->location_id) $bonus += 12.0;

        $colorA = trim(mb_strtolower((string) $a->color));
        $colorB = trim(mb_strtolower((string) $b->color));
        if ($colorA !== '' && $colorB !== '' && $colorA === $colorB) $bonus += 6.0;

        // Brand model exact match
        $bmA = trim(mb_strtolower((string) $a->brand_model));
        $bmB = trim(mb_strtolower((string) $b->brand_model));
        if ($bmA !== '' && $bmB !== '' && $bmA === $bmB) $bonus += 6.0;

        // Incident date proximity (max 10 pts)
        if ($a->incident_date && $b->incident_date) {
            try {
                $da = new \DateTime((string) $a->incident_date);
                $db = new \DateTime((string) $b->incident_date);
                $diffDays = (int) $da->diff($db)->format('%a');
                $bonus += max(0.0, 10.0 - (float) $diffDays);
            } catch (\Throwable $e) {}
        }

        // Type sanity: lost vs found should be opposite already; keep small safety
        if ($a->report_type !== $b->report_type) $bonus += 2.0;

        return round(min(100.0, $base + $bonus), 2);
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

    private function storeReportPhoto(int $reportId, $file): void
    {
        $path = $file->store('report_photos', 'public');
        // Store relative path so asset() helper works with any domain/port
        $url  = 'storage/' . $path;

        ReportPhoto::create([
            'report_id'  => $reportId,
            'photo_url'  => $url,
            'caption'    => null,
            'created_at' => now(),
        ]);
    }

    private function tryDeletePublicUrlFile(?string $url): void
    {
        if (!$url) return;

        // If url is like /storage/report_photos/xxx.jpg or http://host/storage/...
        $pos = strpos($url, '/storage/');
        if ($pos === false) return;

        $relative = substr($url, $pos + strlen('/storage/')); // report_photos/xxx.jpg
        if ($relative === '') return;

        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}
