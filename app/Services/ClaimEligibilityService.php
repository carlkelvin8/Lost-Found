<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\ReportMatch;
use App\Models\User;

/**
 * Single source of truth for "who may submit a claim against a FOUND report".
 *
 * Business rule:
 *   - The finder (reporter of the FOUND report) is the report owner, never a claimant.
 *   - The claimant is the user who filed the LOST report that matches the FOUND report.
 *
 * Both the HTTP layer (ClaimController) and the view layer (reports/show.blade.php)
 * must call this service so the UI can never advertise an action the backend rejects.
 */
class ClaimEligibilityService
{
    /** Report statuses that no longer accept claims (also used to lock report edits). */
    public const LOCKED_STATUSES = ['returned', 'archived'];

    /** report_matches.status values that still represent a live lost<->found pairing. */
    public const ACTIVE_MATCH_STATUSES = ['suggested', 'confirmed'];

    /** Claim statuses that block a new claim from the same claimant. */
    public const ACTIVE_CLAIM_STATUSES = ['pending', 'approved'];

    /**
     * Evaluate whether $user may submit a claim against $report.
     */
    public function evaluate(ItemReport $report, ?User $user): ClaimEligibility
    {
        if (! $user) {
            return ClaimEligibility::ineligible('unauthenticated', 'You must be signed in to submit a claim.');
        }

        if ($report->report_type !== 'found') {
            return ClaimEligibility::ineligible('not_a_found_report', 'Only found reports can be claimed.');
        }

        if (in_array((string) $report->status, self::LOCKED_STATUSES, true)) {
            return ClaimEligibility::ineligible('report_locked', 'Report is not claimable.');
        }

        // The finder owns the report and must never claim it.
        if ((int) $report->reporter_user_id === (int) $user->id) {
            return ClaimEligibility::ineligible('is_reporter', 'You cannot claim your own found report.');
        }

        $lostReport = $this->findMatchedLostReport($report, $user);

        if (! $lostReport) {
            return ClaimEligibility::ineligible(
                'no_matched_lost_report',
                'You can only claim a found item that matches a lost report you filed.'
            );
        }

        if ($this->hasActiveClaim($report, $user)) {
            return ClaimEligibility::ineligible('duplicate_claim', 'Duplicate claim');
        }

        return ClaimEligibility::eligible($lostReport);
    }

    public function canClaim(ItemReport $report, ?User $user): bool
    {
        return $this->evaluate($report, $user)->allowed;
    }

    /**
     * The claimant's own LOST report linked to this FOUND report.
     *
     * Reuses the relationships the project already maintains:
     *   - report_matches rows (lost_report_id -> found_report_id) written by
     *     ItemReportController::runAutoMatch()/upsertMatch(), MatchService and
     *     ReportMatchController::createManual()
     *   - the reciprocal item_reports.matched_report_id pointer
     */
    public function findMatchedLostReport(ItemReport $foundReport, User $user): ?ItemReport
    {
        $matchedLostIds = ReportMatch::query()
            ->where('found_report_id', $foundReport->id)
            ->whereIn('status', self::ACTIVE_MATCH_STATUSES)
            ->pluck('lost_report_id');

        return ItemReport::query()
            ->where('reporter_user_id', $user->id)
            ->where('report_type', 'lost')
            ->where(function ($query) use ($foundReport, $matchedLostIds) {
                $query->whereIn('id', $matchedLostIds)
                    ->orWhere('matched_report_id', $foundReport->id);
            })
            ->orderByDesc('matched_score')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * May $user open this FOUND report's detail page?
     *
     * The Lost reporter needs to inspect the found item in order to claim it.
     * Matching alone grants read access; it never grants edit rights.
     */
    public function canViewFoundReport(ItemReport $report, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ((int) $report->reporter_user_id === (int) $user->id) {
            return true;
        }

        if ($report->report_type !== 'found') {
            return false;
        }

        return $this->findMatchedLostReport($report, $user) !== null;
    }

    /**
     * FOUND reports matched to this LOST report that $user may claim right now.
     *
     * Used to give the Lost reporter a claim action without exposing any match
     * internals (counterpart report id, score, match method) on their own report.
     *
     * @return array<int, int>
     */
    public function claimableFoundReportIds(ItemReport $report, ?User $user, bool $isStaff = false): array
    {
        if ($isStaff || ! $user) {
            return [];
        }

        if ($report->report_type !== 'lost') {
            return [];
        }

        $candidateIds = ReportMatch::query()
            ->where('lost_report_id', $report->id)
            ->whereIn('status', self::ACTIVE_MATCH_STATUSES)
            ->pluck('found_report_id');

        // Honour the reciprocal pointer as well, same as findMatchedLostReport().
        if ($report->matched_report_id) {
            $candidateIds = $candidateIds->push($report->matched_report_id);
        }

        return ItemReport::whereIn('id', $candidateIds->unique())
            ->get()
            ->filter(fn (ItemReport $found) => $this->canClaim($found, $user))
            ->map(fn (ItemReport $found) => (int) $found->id)
            ->values()
            ->all();
    }

    private function hasActiveClaim(ItemReport $report, User $user): bool
    {
        return Claim::query()
            ->where('report_id', $report->id)
            ->where('claimant_user_id', $user->id)
            ->whereIn('status', self::ACTIVE_CLAIM_STATUSES)
            ->exists();
    }
}
