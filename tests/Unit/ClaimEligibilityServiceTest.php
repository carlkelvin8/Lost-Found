<?php

namespace Tests\Unit;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\ReportMatch;
use App\Models\User;
use App\Services\ClaimEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Direct coverage of the centralized eligibility rule.
 *
 * Lost reporter = claimant. Found reporter = finder, never a claimant.
 */
class ClaimEligibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClaimEligibilityService $service;

    private User $lostReporter;

    private User $foundReporter;

    private ItemReport $lostReport;

    private ItemReport $foundReport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ClaimEligibilityService::class);

        $this->lostReporter = User::factory()->create();
        $this->foundReporter = User::factory()->create();

        $this->lostReport = ItemReport::factory()->lost()->for($this->lostReporter, 'reporter')->create();
        $this->foundReport = ItemReport::factory()->found()->for($this->foundReporter, 'reporter')->create();

        ReportMatch::factory()->confirmed()->create([
            'lost_report_id' => $this->lostReport->id,
            'found_report_id' => $this->foundReport->id,
        ]);
    }

    public function test_the_lost_reporter_is_eligible(): void
    {
        $result = $this->service->evaluate($this->foundReport, $this->lostReporter);

        $this->assertTrue($result->allowed);
        $this->assertSame('eligible', $result->reason);
        $this->assertSame($this->lostReport->id, $result->lostReportId());
    }

    public function test_eligibility_never_resolves_the_lost_report_from_the_found_reporter(): void
    {
        $result = $this->service->evaluate($this->foundReport, $this->lostReporter);

        $this->assertNotSame($this->foundReporter->id, $result->lostReportId());
    }

    public function test_the_found_reporter_is_ineligible(): void
    {
        $result = $this->service->evaluate($this->foundReport, $this->foundReporter);

        $this->assertTrue($result->isIneligible());
        $this->assertSame('is_reporter', $result->reason);
        $this->assertFalse($this->service->canClaim($this->foundReport, $this->foundReporter));
    }

    public function test_a_guest_is_ineligible(): void
    {
        $result = $this->service->evaluate($this->foundReport, null);

        $this->assertSame('unauthenticated', $result->reason);
    }

    public function test_a_lost_report_is_never_claimable(): void
    {
        $result = $this->service->evaluate($this->lostReport, $this->foundReporter);

        $this->assertSame('not_a_found_report', $result->reason);
    }

    #[DataProvider('lockedStatusProvider')]
    public function test_locked_found_reports_are_ineligible(string $status): void
    {
        $this->foundReport->update(['status' => $status]);

        $this->assertSame('report_locked', $this->service->evaluate($this->foundReport, $this->lostReporter)->reason);
    }

    public static function lockedStatusProvider(): array
    {
        return [
            'returned' => ['returned'],
            'archived' => ['archived'],
        ];
    }

    #[DataProvider('activeStatusProvider')]
    public function test_active_found_reports_remain_claimable(string $status): void
    {
        $this->foundReport->update(['status' => $status]);

        $this->assertTrue($this->service->canClaim($this->foundReport, $this->lostReporter));
    }

    public static function activeStatusProvider(): array
    {
        return [
            'pending' => ['pending'],
            'matched' => ['matched'],
            'claimed' => ['claimed'],
        ];
    }

    public function test_a_user_without_a_matched_lost_report_is_ineligible(): void
    {
        $stranger = User::factory()->create();

        $this->assertSame(
            'no_matched_lost_report',
            $this->service->evaluate($this->foundReport, $stranger)->reason
        );
    }

    public function test_the_reciprocal_matched_report_pointer_also_grants_eligibility(): void
    {
        // Pairing expressed only via item_reports.matched_report_id (no match row).
        $pointerLost = ItemReport::factory()->lost()->for($this->lostReporter, 'reporter')->create();
        $pointerFound = ItemReport::factory()->found()->for($this->foundReporter, 'reporter')->create();
        $pointerLost->update(['matched_report_id' => $pointerFound->id]);

        $this->assertTrue($this->service->canClaim($pointerFound, $this->lostReporter));
        $this->assertSame($pointerLost->id, $this->service->evaluate($pointerFound, $this->lostReporter)->lostReportId());
    }

    public function test_a_rejected_match_does_not_grant_eligibility(): void
    {
        ReportMatch::where('found_report_id', $this->foundReport->id)->update(['status' => 'rejected']);

        $this->assertSame(
            'no_matched_lost_report',
            $this->service->evaluate($this->foundReport, $this->lostReporter)->reason
        );
    }

    #[DataProvider('activeClaimStatusProvider')]
    public function test_an_existing_active_claim_blocks_a_new_one(string $status): void
    {
        Claim::factory()->create([
            'report_id' => $this->foundReport->id,
            'claimant_user_id' => $this->lostReporter->id,
            'status' => $status,
        ]);

        $this->assertSame('duplicate_claim', $this->service->evaluate($this->foundReport, $this->lostReporter)->reason);
    }

    public static function activeClaimStatusProvider(): array
    {
        return [
            'pending' => ['pending'],
            'approved' => ['approved'],
        ];
    }

    #[DataProvider('inactiveClaimStatusProvider')]
    public function test_a_closed_claim_allows_claiming_again(string $status): void
    {
        Claim::factory()->create([
            'report_id' => $this->foundReport->id,
            'claimant_user_id' => $this->lostReporter->id,
            'status' => $status,
        ]);

        $this->assertTrue($this->service->canClaim($this->foundReport, $this->lostReporter));
    }

    public static function inactiveClaimStatusProvider(): array
    {
        return [
            'rejected' => ['rejected'],
            'cancelled' => ['cancelled'],
        ];
    }

    public function test_another_users_claim_does_not_block_the_claimant(): void
    {
        Claim::factory()->create([
            'report_id' => $this->foundReport->id,
            'claimant_user_id' => $this->foundReporter->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($this->service->canClaim($this->foundReport, $this->lostReporter));
    }

    public function test_view_permission_is_limited_to_the_matched_pair(): void
    {
        $this->assertTrue($this->service->canViewFoundReport($this->foundReport, $this->lostReporter));
        $this->assertTrue($this->service->canViewFoundReport($this->foundReport, $this->foundReporter));
        $this->assertFalse($this->service->canViewFoundReport($this->foundReport, User::factory()->create()));
        $this->assertFalse($this->service->canViewFoundReport($this->foundReport, null));
    }

    public function test_matching_never_grants_view_access_to_a_lost_report(): void
    {
        $stranger = User::factory()->create();

        $this->assertFalse($this->service->canViewFoundReport($this->lostReport, $stranger));
    }

    /* =====================================================
     | claimableFoundReportIds() - the student's claim entry
     | point that does not expose any match internals
     ===================================================== */

    public function test_claimable_found_ids_returns_the_matched_found_report(): void
    {
        $this->assertSame(
            [$this->foundReport->id],
            $this->service->claimableFoundReportIds($this->lostReport, $this->lostReporter)
        );
    }

    public function test_claimable_found_ids_is_empty_for_a_found_report(): void
    {
        // Only a LOST report can be the claimant's side.
        $this->assertSame([], $this->service->claimableFoundReportIds($this->foundReport, $this->foundReporter));
    }

    public function test_claimable_found_ids_is_empty_for_an_unmatched_lost_report(): void
    {
        $lonely = ItemReport::factory()->lost()->for($this->lostReporter, 'reporter')->create();

        $this->assertSame([], $this->service->claimableFoundReportIds($lonely, $this->lostReporter));
    }

    public function test_claimable_found_ids_is_empty_for_staff(): void
    {
        $this->assertSame([], $this->service->claimableFoundReportIds($this->lostReport, $this->lostReporter, true));
    }

    public function test_claimable_found_ids_is_empty_for_a_stranger(): void
    {
        $stranger = User::factory()->create();

        $this->assertSame([], $this->service->claimableFoundReportIds($this->lostReport, $stranger));
    }

    public function test_claimable_found_ids_excludes_a_locked_found_report(): void
    {
        $this->foundReport->update(['status' => 'returned']);

        $this->assertSame([], $this->service->claimableFoundReportIds($this->lostReport, $this->lostReporter));
    }

    public function test_claimable_found_ids_excludes_a_rejected_match(): void
    {
        ReportMatch::query()->update(['status' => 'rejected']);

        $this->assertSame([], $this->service->claimableFoundReportIds($this->lostReport, $this->lostReporter));
    }

    public function test_claimable_found_ids_excludes_an_already_claimed_found_report(): void
    {
        Claim::factory()->create([
            'report_id' => $this->foundReport->id,
            'claimant_user_id' => $this->lostReporter->id,
            'status' => 'pending',
        ]);

        $this->assertSame([], $this->service->claimableFoundReportIds($this->lostReport, $this->lostReporter));
    }

    public function test_claimable_found_ids_follows_the_reciprocal_matched_report_pointer(): void
    {
        // No report_matches row - only item_reports.matched_report_id is set.
        ReportMatch::query()->delete();
        $this->lostReport->update(['matched_report_id' => $this->foundReport->id]);

        $this->assertSame(
            [$this->foundReport->id],
            $this->service->claimableFoundReportIds($this->lostReport, $this->lostReporter)
        );
    }

    public function test_claimable_found_ids_excludes_the_users_own_found_report(): void
    {
        // The finder must never be offered a claim on their own report.
        $this->assertSame([], $this->service->claimableFoundReportIds($this->foundReport, $this->foundReporter));
    }
}
