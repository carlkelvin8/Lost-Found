<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ItemReport;
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

        if ($report->report_type !== 'found') {
            return redirect()
                ->route('reports.show', $reportId)
                ->withErrors(['message' => 'Only found reports can be claimed']);
        }

        if (in_array($report->status, ['returned', 'archived'], true)) {
            return redirect()
                ->route('reports.show', $reportId)
                ->withErrors(['message' => 'Report is not claimable']);
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
        ]);

        $report = ItemReport::findOrFail((int) $data['report_id']);

        if ($report->report_type !== 'found') {
            return back()->withErrors(['message' => 'Only found reports can be claimed'])->withInput();
        }

        if (in_array($report->status, ['returned', 'archived'], true)) {
            return back()->withErrors(['message' => 'Report is not claimable'])->withInput();
        }

        $claimantId = (int) $this->user()->id;

        // Prevent duplicate active claims
        $exists = Claim::where('report_id', $report->id)
            ->where('claimant_user_id', $claimantId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['message' => 'Duplicate claim'])->withInput();
        }

        $claimId = null;

        try {
            DB::transaction(function () use ($request, $report, $data, $claimantId, &$claimId) {
                $claim = Claim::create([
                    'report_id' => $report->id,
                    'claimant_user_id' => $claimantId,
                    'proof_text' => $data['proof_text'],
                    'status' => 'pending',
                    'reviewed_by_user_id' => null,
                    'reviewed_at' => null,
                ]);

                $claimId = $claim->id;

                $oldStatus = $report->status;
                $report->update(['status' => 'claimed']);

                // SAFE side-effects
                $this->pushReportStatus(
                    $report->id,
                    $oldStatus,
                    'claimed',
                    $claimantId,
                    'Claim submitted'
                );

                $this->notify(
                    (int) $report->reporter_user_id,
                    'claim',
                    'Claim submitted',
                    'A user submitted a claim for your found report.',
                    [
                        'report_id' => $report->id,
                        'claim_id' => $claim->id,
                    ]
                );

                $this->audit(
                    $request,
                    'claims.create',
                    'claims',
                    $claim->id,
                    ['report_id' => $report->id]
                );
            });
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

        $documents = DB::table('claim_documents')
            ->where('claim_id', $claim->id)
            ->orderByDesc('id')
            ->get();

        $report = ItemReport::findOrFail((int) $claim->report_id);

        return view('claims.show', compact(
            'claim',
            'documents',
            'report',
            'isStaff',
            'isOwner'
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

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $claim, $data) {
            $report = ItemReport::findOrFail($claim->report_id);

            $claim->update([
                'status' => 'rejected',
                'reviewed_by_user_id' => (int) $this->user()->id,
                'reviewed_at' => now(),
            ]);

            $newStatus = $report->matched_report_id ? 'matched' : 'pending';
            $report->update(['status' => $newStatus]);
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

            $newStatus = $report->matched_report_id ? 'matched' : 'pending';
            $report->update(['status' => $newStatus]);
        });

        return redirect()->route('claims.show', $id)->with('success', 'Cancelled');
    }
}
