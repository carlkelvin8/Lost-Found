<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\ReportMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Lost & Found claim workflow.
 *
 * Account A files the LOST report  -> A is the claimant.
 * Account B files the FOUND report -> B is the finder/report owner, never a claimant.
 */
class ClaimEligibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Distinctive markup that only the Matches card emits. Asserting on the card
     * title alone is unreliable, so this pins the icon + title pair.
     */
    private const MATCHES_CARD_MARKER = 'bi-link-45deg"></i> Matches';

    private User $lostReporter;   // Account A

    private User $foundReporter;  // Account B

    private User $unrelated;      // Account C

    private ItemReport $lostReport;

    private ItemReport $foundReport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lostReporter = User::factory()->create();
        $this->foundReporter = User::factory()->create();
        $this->unrelated = User::factory()->create();

        $this->lostReport = ItemReport::factory()
            ->lost()
            ->for($this->lostReporter, 'reporter')
            ->create(['item_name' => 'Lost Wallet']);

        $this->foundReport = ItemReport::factory()
            ->found()
            ->for($this->foundReporter, 'reporter')
            ->create(['item_name' => 'Found Wallet', 'status' => 'matched']);

        ReportMatch::factory()->create([
            'lost_report_id' => $this->lostReport->id,
            'found_report_id' => $this->foundReport->id,
            'score' => 95,
            'status' => 'confirmed',
        ]);
    }

    private function validProof(): array
    {
        return ['proof_text' => 'This is my wallet, it contains my student ID and cards.'];
    }

    /* =====================================================
     | SCENARIO 1 - who sees the Submit Claim action
     ===================================================== */

    public function test_lost_reporter_sees_submit_claim_on_the_matched_found_report(): void
    {
        $response = $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->foundReport->id));

        $response->assertOk();
        $response->assertSee('Submit Claim');
    }

    public function test_found_reporter_does_not_see_submit_claim_on_their_own_found_report(): void
    {
        $response = $this->actingAs($this->foundReporter)
            ->get(route('reports.show', $this->foundReport->id));

        $response->assertOk();
        $response->assertDontSee('Submit Claim');
    }

    public function test_found_reporter_cannot_edit_a_matched_report(): void
    {
        $response = $this->actingAs($this->foundReporter)
            ->get(route('reports.show', $this->foundReport->id));

        $response->assertOk();
        $response->assertSee('Locked');
        $response->assertDontSee(route('reports.edit', $this->foundReport->id), false);
        $response->assertDontSee('Submit Claim');
    }

    public function test_found_reporter_sees_the_claims_history_on_their_own_found_report(): void
    {
        Claim::factory()->create([
            'report_id' => $this->foundReport->id,
            'claimant_user_id' => $this->lostReporter->id,
        ]);

        $this->actingAs($this->foundReporter)
            ->get(route('reports.show', $this->foundReport->id))
            ->assertOk()
            ->assertSee('Claims History');
    }

    /* =====================================================
     | SCENARIO 2 - claim form access
     ===================================================== */

    public function test_lost_reporter_can_open_the_claim_form(): void
    {
        $response = $this->actingAs($this->lostReporter)
            ->get(route('claims.create', $this->foundReport->id));

        $response->assertOk();
        $response->assertSee('Submit Claim');
    }

    public function test_found_reporter_is_redirected_away_from_the_claim_form(): void
    {
        $response = $this->actingAs($this->foundReporter)
            ->from(route('reports.show', $this->foundReport->id))
            ->get(route('claims.create', $this->foundReport->id));

        $response->assertRedirect(route('reports.show', $this->foundReport->id));
        $response->assertSessionHasErrors('message');
    }

    /* =====================================================
     | SCENARIO 3 - claim is persisted against the claimant
     ===================================================== */

    public function test_lost_reporter_claim_is_stored_against_the_claimant_and_found_report(): void
    {
        $response = $this->actingAs($this->lostReporter)
            ->post(route('claims.store'), $this->validProof() + [
                'report_id' => $this->foundReport->id,
            ]);

        $claim = Claim::sole();

        $response->assertRedirect(route('claims.show', $claim->id));

        // The claimant must be Account A (the LOST reporter)...
        $this->assertSame($this->lostReporter->id, (int) $claim->claimant_user_id);
        // ...and never the finder.
        $this->assertNotSame($this->foundReporter->id, (int) $claim->claimant_user_id);

        // Report linkage points at the FOUND report.
        $this->assertSame($this->foundReport->id, (int) $claim->report_id);
        $this->assertSame('pending', $claim->status);

        // Found report moves to the claimed state; the Lost report is left alone.
        $this->assertSame('claimed', $this->foundReport->fresh()->status);
        $this->assertSame('pending', $this->lostReport->fresh()->status);
    }

    /* =====================================================
     | SCENARIO 4 - state survives a reload / refetch
     ===================================================== */

    public function test_claim_state_is_correct_after_reload(): void
    {
        $this->actingAs($this->lostReporter)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id]);

        $claim = Claim::sole();

        // Re-fetching the report page no longer advertises a second claim.
        $response = $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->foundReport->id));
        $response->assertOk();
        $response->assertDontSee('Submit Claim');

        // The claimant can still open their claim.
        $this->actingAs($this->lostReporter)
            ->get(route('claims.show', $claim->id))
            ->assertOk();

        // And it is listed under "my claims".
        $this->actingAs($this->lostReporter)
            ->get(route('claims.index'))
            ->assertOk()
            ->assertSee('Report #'.$this->foundReport->id, false);
    }

    /* =====================================================
     | SCENARIO 5 - duplicate prevention
     ===================================================== */

    public function test_duplicate_claim_from_the_same_claimant_is_rejected(): void
    {
        $this->actingAs($this->lostReporter)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id]);

        $this->assertSame(1, Claim::count());

        $response = $this->actingAs($this->lostReporter)
            ->from(route('reports.show', $this->foundReport->id))
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id]);

        $response->assertSessionHasErrors('message');
        $this->assertSame(1, Claim::count());
    }

    public function test_an_approved_claim_also_blocks_a_second_claim(): void
    {
        Claim::factory()->create([
            'report_id' => $this->foundReport->id,
            'claimant_user_id' => $this->lostReporter->id,
            'status' => 'approved',
        ]);

        $this->actingAs($this->lostReporter)
            ->from(route('reports.show', $this->foundReport->id))
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id])
            ->assertSessionHasErrors('message');

        $this->assertSame(1, Claim::count());
    }

    public function test_a_rejected_claim_also_blocks_a_further_claim(): void
    {
        Claim::factory()->create([
            'report_id' => $this->foundReport->id,
            'claimant_user_id' => $this->lostReporter->id,
            'status' => 'rejected',
        ]);

        $this->actingAs($this->lostReporter)
            ->from(route('reports.show', $this->foundReport->id))
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id])
            ->assertSessionHasErrors('message');

        $this->assertSame(1, Claim::count());
    }

    /* =====================================================
     | SCENARIO 6 - backend rejects the finder (direct call)
     ===================================================== */

    public function test_found_reporter_cannot_submit_a_claim_against_their_own_found_report(): void
    {
        $response = $this->actingAs($this->foundReporter)
            ->from(route('reports.show', $this->foundReport->id))
            ->post(route('claims.store'), $this->validProof() + [
                'report_id' => $this->foundReport->id,
            ]);

        $response->assertSessionHasErrors('message');
        $this->assertSame(0, Claim::count());
        $this->assertSame('matched', $this->foundReport->fresh()->status);
    }

    /* =====================================================
     | SCENARIO 7 - unrelated account C
     ===================================================== */

    public function test_unrelated_user_cannot_view_or_claim_the_found_report(): void
    {
        $this->actingAs($this->unrelated)
            ->get(route('reports.show', $this->foundReport->id))
            ->assertForbidden();

        $this->actingAs($this->unrelated)
            ->from(route('dashboard'))
            ->post(route('claims.store'), $this->validProof() + [
                'report_id' => $this->foundReport->id,
            ])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Claim::count());
    }

    /* =====================================================
     | SCENARIO 8 - found report with no related lost report
     ===================================================== */

    public function test_found_report_without_a_matched_lost_report_is_not_claimable(): void
    {
        $lonelyFound = ItemReport::factory()
            ->found()
            ->for($this->foundReporter, 'reporter')
            ->create(['item_name' => 'Unmatched Umbrella']);

        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $lonelyFound->id))
            ->assertForbidden();

        $this->actingAs($this->lostReporter)
            ->from(route('dashboard'))
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $lonelyFound->id])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Claim::count());
    }

    public function test_a_rejected_match_does_not_grant_eligibility(): void
    {
        ReportMatch::where('found_report_id', $this->foundReport->id)->update(['status' => 'rejected']);

        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->foundReport->id))
            ->assertForbidden();

        $this->assertSame(0, Claim::count());
    }

    public function test_another_users_lost_report_does_not_grant_eligibility(): void
    {
        // C files an unrelated lost report; the pairing still belongs to A.
        ItemReport::factory()->lost()->for($this->unrelated, 'reporter')->create();

        $this->actingAs($this->unrelated)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Claim::count());
    }

    /* =====================================================
     | SCENARIO 9 - non-claimable statuses
     ===================================================== */

    public function test_returned_found_report_is_not_claimable(): void
    {
        $this->foundReport->update(['status' => 'returned']);

        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->foundReport->id))
            ->assertOk()
            ->assertDontSee('Submit Claim');

        $this->actingAs($this->lostReporter)
            ->from(route('reports.show', $this->foundReport->id))
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Claim::count());
    }

    public function test_archived_found_report_is_not_claimable(): void
    {
        $this->foundReport->update(['status' => 'archived']);

        $this->actingAs($this->lostReporter)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Claim::count());
    }

    /* =====================================================
     | LOST reports are never claimable
     ===================================================== */

    public function test_lost_reports_can_never_be_claimed(): void
    {
        $this->actingAs($this->unrelated)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->lostReport->id])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Claim::count());
    }

    /* =====================================================
     | EXISTING WORKFLOW STILL WORKS
     ===================================================== */

    public function test_a_valid_claim_remains_visible_to_staff_for_review(): void
    {
        $this->actingAs($this->lostReporter)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id]);

        $claim = Claim::sole();

        $staff = User::factory()->create();
        $staff->roles()->firstOrCreate(['name' => 'osa']);

        $this->actingAs($staff)
            ->get(route('claims.show', $claim->id))
            ->assertOk();

        $this->actingAs($staff)
            ->post(route('claims.approve', $claim->id))
            ->assertRedirect(route('claims.show', $claim->id));

        $this->assertSame('approved', $claim->fresh()->status);
        $this->assertSame('returned', $this->foundReport->fresh()->status);
    }

    public function test_a_third_party_cannot_view_someone_elses_claim(): void
    {
        $this->actingAs($this->lostReporter)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id]);

        $claim = Claim::sole();

        $this->actingAs($this->unrelated)
            ->get(route('claims.show', $claim->id))
            ->assertForbidden();
    }

    public function test_claimant_can_cancel_their_own_pending_claim(): void
    {
        $this->actingAs($this->lostReporter)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id]);

        $claim = Claim::sole();

        $this->actingAs($this->lostReporter)
            ->post(route('claims.cancel', $claim->id))
            ->assertRedirect(route('claims.show', $claim->id));

        $this->assertSame('cancelled', $claim->fresh()->status);
        $this->assertSame('matched', $this->foundReport->fresh()->status);
    }

    /* =====================================================
     | Report access control is unchanged for non-matches
     ===================================================== */

    public function test_a_user_cannot_open_a_found_report_they_have_no_link_to(): void
    {
        $strangerFound = ItemReport::factory()->found()->create();

        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $strangerFound->id))
            ->assertForbidden();
    }

    public function test_matching_does_not_grant_edit_rights_on_the_found_report(): void
    {
        // A can READ the found report...
        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->foundReport->id))
            ->assertOk()
            ->assertDontSee(route('reports.edit', $this->foundReport->id), false);

        // ...but cannot EDIT it.
        $this->actingAs($this->lostReporter)
            ->get(route('reports.edit', $this->foundReport->id))
            ->assertForbidden();
    }

    public function test_the_lost_reporter_can_reach_the_claimable_found_report_from_their_own_report(): void
    {
        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->lostReport->id))
            ->assertOk()
            ->assertSee(route('claims.create', $this->foundReport->id), false);

        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->foundReport->id))
            ->assertOk()
            ->assertSee('Submit Claim');
    }

    /* =====================================================
     | DISCOVERABILITY - the claim action on the Lost report
     | (students get a plain button, never the Matches card)
     ===================================================== */

    public function test_lost_reporter_gets_a_submit_claim_action_on_their_lost_report(): void
    {
        $response = $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->lostReport->id));

        $response->assertOk();
        // The Lost reporter must be able to claim without hunting for the counterpart.
        $response->assertSee('Submit Claim');
        $response->assertSee(route('claims.create', $this->foundReport->id), false);
    }

    public function test_finder_gets_no_submit_claim_action(): void
    {
        $response = $this->actingAs($this->foundReporter)
            ->get(route('reports.show', $this->foundReport->id));

        $response->assertOk();
        $response->assertDontSee(route('claims.create', $this->foundReport->id), false);
        $response->assertDontSee('Submit Claim');
    }

    public function test_submit_claim_action_disappears_once_the_claim_exists(): void
    {
        $this->actingAs($this->lostReporter)
            ->post(route('claims.store'), $this->validProof() + ['report_id' => $this->foundReport->id]);

        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->lostReport->id))
            ->assertOk()
            ->assertDontSee('Submit Claim');
    }

    public function test_submit_claim_action_is_absent_for_a_locked_found_report(): void
    {
        $this->foundReport->update(['status' => 'returned']);

        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->lostReport->id))
            ->assertOk()
            ->assertDontSee('Submit Claim');
    }

    public function test_submit_claim_action_is_absent_for_an_unrelated_viewer(): void
    {
        // Account C cannot even open the counterpart report.
        $this->actingAs($this->unrelated)
            ->get(route('reports.show', $this->foundReport->id))
            ->assertForbidden();

        // ...and on their own report page no foreign claim action appears.
        $ownReport = ItemReport::factory()->lost()->for($this->unrelated, 'reporter')->create();

        $this->actingAs($this->unrelated)
            ->get(route('reports.show', $ownReport->id))
            ->assertOk()
            ->assertDontSee('Submit Claim');
    }

    public function test_submit_claim_action_appears_for_each_claimable_found_report(): void
    {
        $secondFound = ItemReport::factory()->found()->for($this->unrelated, 'reporter')->create(['status' => 'matched']);
        ReportMatch::factory()->create([
            'lost_report_id' => $this->lostReport->id,
            'found_report_id' => $secondFound->id,
            'score' => 60,
            'status' => 'suggested',
        ]);

        $ids = $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->lostReport->id))
            ->viewData('claimableFoundIds');

        $this->assertEqualsCanonicalizing(
            [$this->foundReport->id, $secondFound->id],
            $ids
        );
    }

    /* =====================================================
     | MATCH VISIBILITY - match internals are staff-only
     ===================================================== */

    /**
     * Counterpart report ids rendered in the Matches card for $user on $report.
     *
     * @return array<int, int>
     */
    private function visibleCounterparts(User $user, ItemReport $report): array
    {
        return $this->actingAs($user)
            ->get(route('reports.show', $report->id))
            ->viewData('matches')
            ->map(fn ($match) => (int) ($match->lost_report_id === $report->id
                ? $match->found_report_id
                : $match->lost_report_id))
            ->all();
    }

    public function test_a_student_sees_no_matches_card_on_their_lost_report(): void
    {
        $response = $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->lostReport->id));

        $response->assertOk();
        $response->assertDontSee(self::MATCHES_CARD_MARKER, false);
        // No match internals leak: neither the counterpart nor the score/method.
        $response->assertDontSee(route('reports.show', $this->foundReport->id), false);

        $score = ReportMatch::sole()->score;
        $response->assertDontSee('Score '.$score);

        $this->assertSame([], $this->visibleCounterparts($this->lostReporter, $this->lostReport));
    }

    public function test_a_student_sees_no_matches_card_on_their_found_report(): void
    {
        // Owning the FOUND report must not expose Account A's LOST report.
        $response = $this->actingAs($this->foundReporter)
            ->get(route('reports.show', $this->foundReport->id));

        $response->assertOk();
        $response->assertDontSee(self::MATCHES_CARD_MARKER, false);
        $response->assertDontSee('Report #'.$this->lostReport->id);
        $response->assertDontSee(route('reports.show', $this->lostReport->id), false);
        $this->assertSame([], $this->visibleCounterparts($this->foundReporter, $this->foundReport));
    }

    public function test_the_counterpart_found_report_is_still_reachable_for_claiming(): void
    {
        // Hiding the Matches card must not cost the student access to the item
        // they are entitled to claim.
        $this->actingAs($this->lostReporter)
            ->get(route('reports.show', $this->foundReport->id))
            ->assertOk()
            ->assertSee('Submit Claim');
    }

    public function test_staff_still_see_every_match_in_the_card(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->firstOrCreate(['name' => 'admin']);

        $response = $this->actingAs($admin)->get(route('reports.show', $this->foundReport->id));

        $response->assertOk();
        $response->assertSee(self::MATCHES_CARD_MARKER, false);
        $this->assertSame(
            [$this->lostReport->id],
            $this->visibleCounterparts($admin, $this->foundReport)
        );
    }

    public function test_staff_never_get_a_claim_button(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->firstOrCreate(['name' => 'admin']);

        $response = $this->actingAs($admin)->get(route('reports.show', $this->foundReport->id));

        $response->assertOk();
        $response->assertDontSee('Submit Claim');
        $this->assertSame([], $response->viewData('claimableFoundIds'));
    }

    public function test_an_osa_officer_also_keeps_the_matches_card(): void
    {
        $officer = User::factory()->create();
        $officer->roles()->firstOrCreate(['name' => 'osa']);

        $this->assertSame(
            [$this->lostReport->id],
            $this->visibleCounterparts($officer, $this->foundReport)
        );
    }

    public function test_a_students_unmatched_lost_report_shows_no_claim_action(): void
    {
        $lonely = ItemReport::factory()->lost()->for($this->lostReporter, 'reporter')->create();

        $response = $this->actingAs($this->lostReporter)->get(route('reports.show', $lonely->id));

        $response->assertOk();
        $response->assertDontSee('Submit Claim');
        $this->assertSame([], $response->viewData('claimableFoundIds'));
    }
}
