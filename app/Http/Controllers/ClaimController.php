<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\ReportMatch;
use App\Services\ClaimEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClaimController extends WebBaseController
{
    /* =====================================================
     | INDEX – FETCH CLAIMS
     ===================================================== */
    public function index(Request $request)
    {
        if (!$this->user()) {
            return redirect()->route('login');
        }

        $status = (string) $request->query('status', '');

        $isStaff = $this->hasAnyRole(['admin', 'osa']);

        $query = Claim::query()
            ->with(['claimant']) // eager load user
            ->orderByDesc('id');

        // Status filter (strict + safe)
        if ($status !== '') {
            $allowed = ['pending', 'approved', 'rejected', 'cancelled'];
            if (in_array($status, $allowed, true)) {
                $query->where('status', $status);
            } else {
                $status = '';
            }
        }

        // Non-staff users see ONLY their own claims
        if (!$isStaff) {
            $query->where('claimant_user_id', (int) $this->user()->id);
        }

        $claims = $query->paginate(20)->withQueryString();

        return view('claims.index', compact('claims', 'status', 'isStaff'));
    }

    /* =====================================================
     | CREATE – CLAIM FORM
     ===================================================== */
    public function create(Request $request, int $reportId)
    {
        if (!$this->user()) {
            return redirect()->route('login');
        }

        $report = ItemReport::findOrFail($reportId);

        $eligibility = $this->eligibility()->evaluate($report, $this->user());

        if ($eligibility->isIneligible()) {
            return redirect()
                ->route('reports.show', $reportId)
                ->withErrors(['message' => $eligibility->message]);
        }

        return view('claims.create', compact('report'));
    }

    /* =====================================================
     | STORE – CREATE CLAIM
     ===================================================== */
    public function store(Request $request)
    {
        if (!$this->user()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'report_id' => ['required', 'integer', 'exists:item_reports,id'],
            'proof_text' => ['required', 'string', 'min:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'contact_info' => ['nullable', 'string', 'max:255'],
        ]);

        $report = ItemReport::findOrFail((int) $data['report_id']);

        // Centralized authorization: only the LOST reporter matched to this FOUND
        // report may claim it. This rejects the finder claiming their own report,
        // unrelated accounts, duplicates and non-claimable statuses.
        $eligibility = $this->eligibility()->evaluate($report, $this->user());

        if ($eligibility->isIneligible()) {
            return back()->withErrors(['message' => $eligibility->message])->withInput();
        }

        $claimantId = (int) $this->user()->id;

        $lostReportId = $eligibility->lostReportId();

        $claimId = null;

        try {
            DB::transaction(function () use ($request, $report, $data, $claimantId, $lostReportId, &$claimId) {
                // Re-assert eligibility while holding a lock on the report row so a
                // concurrent duplicate submission cannot slip through the pre-check.
                $lockedReport = ItemReport::where('id', $report->id)->lockForUpdate()->firstOrFail();

                $recheck = $this->eligibility()->evaluate($lockedReport, $this->user());

                if ($recheck->isIneligible()) {
                    throw new ClaimRejectedException($recheck->message);
                }

                $claim = Claim::create([
                    'report_id' => $lockedReport->id,
                    'claimant_user_id' => $claimantId,
                    'proof_text' => $data['proof_text'],
                    'status' => 'pending',
                    'reviewed_by_user_id' => null,
                    'reviewed_at' => null,
                ]);

                $claimId = $claim->id;

                $oldStatus = $lockedReport->status;
                $lockedReport->update(['status' => 'claimed']);

                // SAFE side-effects
                $this->pushReportStatus(
                    $lockedReport->id,
                    $oldStatus,
                    'claimed',
                    $claimantId,
                    'Claim submitted'
                );

                $this->notify(
                    (int) $lockedReport->reporter_user_id,
                    'claim',
                    'Claim submitted',
                    'A user submitted a claim for your found report.',
                    [
                        'report_id' => $lockedReport->id,
                        'claim_id' => $claim->id,
                    ]
                );

                $this->audit(
                    $request,
                    'claims.create',
                    'claims',
                    $claim->id,
                    [
                        'report_id' => $lockedReport->id,
                        'lost_report_id' => $lostReportId,
                    ]
                );
            });
        } catch (ClaimRejectedException $e) {
            return back()->withErrors(['message' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            Log::error('CLAIM CREATE FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['message' => 'Claim creation failed'])->withInput();
        }

        return redirect()->route('claims.show', $claimId)->with('success', 'Claim created');
    }

    /* =====================================================
     | SHOW – VIEW CLAIM
     ===================================================== */
    public function show(Request $request, int $id)
    {
        if (!$this->user()) {
            return redirect()->route('login');
        }

        $claim = Claim::with('claimant')->findOrFail($id);

        $isStaff = $this->hasAnyRole(['admin', 'osa']);
        $isOwner = (int) $claim->claimant_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) {
            abort(403);
        }

        $report = ItemReport::findOrFail((int) $claim->report_id);

        // Get match score if available
        $matchScore = null;
        $matchRecommendation = null;
        if ($report->matched_score) {
            $matchScore = (float) $report->matched_score;
        }

        // Calculate AI recommendation based on match score
        if ($matchScore !== null) {
            if ($matchScore >= 70) {
                $matchRecommendation = 'high';  // High confidence - recommend approve
            } elseif ($matchScore >= 45) {
                $matchRecommendation = 'medium'; // Medium confidence - manual review needed
            } else {
                $matchRecommendation = 'low';    // Low confidence - recommend reject
            }
        }

        return view('claims.show', compact(
            'claim',
            'report',
            'isStaff',
            'isOwner',
            'matchScore',
            'matchRecommendation'
        ));
    }

    /* =====================================================
     | APPROVE CLAIM
     ===================================================== */
    public function approve(Request $request, int $id)
    {
        $this->requireAnyRole(['admin', 'osa']);

        $claim = Claim::findOrFail($id);

        if ($claim->status !== 'pending') {
            return back()->withErrors(['message' => 'Claim is not pending']);
        }

        DB::transaction(function () use ($request, $claim) {
            $report = ItemReport::findOrFail($claim->report_id);

            $claim->update([
                'status' => 'approved',
                'reviewed_by_user_id' => (int) $this->user()->id,
                'reviewed_at' => now(),
            ]);

            $old = $report->status;
            $report->update(['status' => 'returned']);

            $this->pushReportStatus(
                $report->id,
                $old,
                'returned',
                (int) $this->user()->id,
                'Claim approved'
            );

            // Notify claimant that their claim was approved
            $this->notify(
                (int) $claim->claimant_user_id,
                'claim',
                'Claim approved',
                'Your claim for report #' . $report->id . ' has been approved.',
                [
                    'report_id' => $report->id,
                    'claim_id' => $claim->id,
                    'status' => 'approved',
                ]
            );

            $this->audit($request, 'claims.approve', 'claims', $claim->id, [
                'report_id' => $report->id,
            ]);
        });

        return redirect()->route('claims.show', $id)->with('success', 'Approved');
    }

    /* =====================================================
     | REJECT CLAIM
     ===================================================== */
    public function reject(Request $request, int $id)
    {
        $this->requireAnyRole(['admin', 'osa']);

        $claim = Claim::findOrFail($id);

        if ($claim->status !== 'pending') {
            return back()->withErrors(['message' => 'Claim is not pending']);
        }

        DB::transaction(function () use ($request, $claim) {
            $report = ItemReport::findOrFail($claim->report_id);

            $claim->update([
                'status' => 'rejected',
                'reviewed_by_user_id' => (int) $this->user()->id,
                'reviewed_at' => now(),
            ]);

            $hasActiveMatches = ReportMatch::where('lost_report_id', $report->id)
                ->orWhere('found_report_id', $report->id)
                ->whereIn('status', ['confirmed', 'suggested'])
                ->exists();
            $newStatus = $hasActiveMatches ? 'matched' : 'pending';
            $report->update(['status' => $newStatus]);

            $this->pushReportStatus(
                $report->id,
                'claimed',
                $newStatus,
                (int) $this->user()->id,
                'Claim rejected'
            );

            // Notify claimant that their claim was rejected
            $this->notify(
                (int) $claim->claimant_user_id,
                'claim',
                'Claim rejected',
                'Your claim for report #' . $report->id . ' has been rejected.',
                [
                    'report_id' => $report->id,
                    'claim_id' => $claim->id,
                    'status' => 'rejected',
                ]
            );

            $this->audit($request, 'claims.reject', 'claims', $claim->id, [
                'report_id' => $report->id,
            ]);
        });

        return redirect()->route('claims.show', $id)->with('success', 'Rejected');
    }

    /* =====================================================
     | CANCEL CLAIM
     ===================================================== */
    public function cancel(Request $request, int $id)
    {
        if (!$this->user()) {
            return redirect()->route('login');
        }

        $claim = Claim::findOrFail($id);

        if ((int) $claim->claimant_user_id !== (int) $this->user()->id) {
            abort(403);
        }

        if ($claim->status !== 'pending') {
            return back()->withErrors(['message' => 'Only pending claims can be cancelled']);
        }

        DB::transaction(function () use ($request, $claim) {
            $report = ItemReport::findOrFail($claim->report_id);

            $claim->update(['status' => 'cancelled']);

            $hasActiveMatches = ReportMatch::where('lost_report_id', $report->id)
                ->orWhere('found_report_id', $report->id)
                ->whereIn('status', ['confirmed', 'suggested'])
                ->exists();
            $newStatus = $hasActiveMatches ? 'matched' : 'pending';
            $report->update(['status' => $newStatus]);

            $this->pushReportStatus(
                $report->id,
                'claimed',
                $newStatus,
                (int) $this->user()->id,
                'Claim cancelled by claimant'
            );

            $this->audit($request, 'claims.cancel', 'claims', $claim->id, [
                'report_id' => $report->id,
            ]);
        });

        return redirect()->route('claims.show', $id)->with('success', 'Cancelled');
    }

    /* =====================================================
     | SUCCESSFUL CLAIMANTS - Admin page
     | Table: claims | Model: Claim | Relationships: claimant, report.category, report.location, reviewer
     | Successful status: approved (existing workflow: approve() sets status=approved, report=returned)
     ===================================================== */
    public function successful(Request $request)
    {
        $this->requireAnyRole(['admin', 'osa']);
        $isStaff = true; // staff-only page

        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id', '');
        $approvedBy = $request->query('approved_by', '');
        $dateFrom = $request->query('date_from', '');
        $dateTo = $request->query('date_to', '');

        $query = Claim::query()
            ->with(['claimant.profile', 'report.category', 'report.location', 'reviewer.profile'])
            ->where('status', 'approved')
            ->orderByDesc('reviewed_at')
            ->orderByDesc('id');

        // Search claimant name / email / item name / claim reference
        if ($q !== '') {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $q);
            $query->where(function ($qq) use ($escaped, $q) {
                $qq->where('id', $q) // claim reference exact match if numeric
                    ->orWhereHas('claimant', function ($cq) use ($escaped) {
                        $cq->where('email', 'like', "%{$escaped}%")
                           ->orWhereHas('profile', fn($pq) => $pq->where('full_name', 'like', "%{$escaped}%"));
                    })
                    ->orWhereHas('report', fn($rq) => $rq->where('item_name', 'like', "%{$escaped}%"));
            });
        }

        // Category filter via report.category_id
        if ($categoryId !== '' && is_numeric($categoryId)) {
            $query->whereHas('report', fn($rq) => $rq->where('category_id', (int) $categoryId));
        }

        // Approved by filter
        if ($approvedBy !== '' && is_numeric($approvedBy)) {
            $query->where('reviewed_by_user_id', (int) $approvedBy);
        }

        // Date range filter on reviewed_at (completed date) or created_at
        if ($dateFrom !== '' && strtotime($dateFrom) !== false) {
            $query->whereDate('reviewed_at', '>=', $dateFrom);
        }
        if ($dateTo !== '' && strtotime($dateTo) !== false) {
            $query->whereDate('reviewed_at', '<=', $dateTo);
        }

        $successful = $query->paginate(20)->withQueryString();

        // Data for filters
        $categories = \App\Models\Category::orderBy('name')->get();
        $approvers = \App\Models\User::whereIn('id', Claim::where('status','approved')->whereNotNull('reviewed_by_user_id')->distinct()->pluck('reviewed_by_user_id'))
            ->with('profile')->get();

        return view('claims.successful', compact('successful', 'q', 'categoryId', 'approvedBy', 'dateFrom', 'dateTo', 'categories', 'approvers', 'isStaff'));
    }

    /* =====================================================
     | CLAIM ELIGIBILITY
     ===================================================== */
    protected function eligibility(): ClaimEligibilityService
    {
        return app(ClaimEligibilityService::class);
    }
}
