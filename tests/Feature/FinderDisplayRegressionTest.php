<?php

namespace Tests\Feature;

use App\Models\ItemReport;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinderDisplayRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->roles()->firstOrCreate(['name' => 'admin']);
        return $user;
    }

    private function student(): User
    {
        $user = User::factory()->create();
        $user->roles()->firstOrCreate(['name' => 'student']);
        UserProfile::create(['user_id' => $user->id, 'full_name' => 'Actual Student Finder', 'school_id_number' => '001-2026-90', 'user_type' => 'student']);
        return $user;
    }

    public function test_legacy_student_found_report_displays_its_reporter_profile_without_surrender_flag(): void
    {
        $student = $this->student();
        $report = ItemReport::factory()->found()->create(['reporter_user_id' => $student->id, 'surrendered_by_student' => false]);
        $this->actingAs($this->admin())->get(route('reports.show', $report))
            ->assertOk()->assertSee('id="finderNameValue">Actual Student Finder', false)
            ->assertSee('id="finderStudentIdValue">001-2026-90', false);
        $this->assertNull($report->fresh()->finder_name); // Display fallback does not rewrite history.
    }

    public function test_new_student_found_reports_snapshot_the_authenticated_finder(): void
    {
        Mail::fake();
        $student = $this->student();
        $this->actingAs($student)->postJson(route('reports.store'), [
            'submission_id' => (string) Str::uuid(), 'report_type' => 'found', 'reporter_name' => 'Actual Student Finder', 'item_description' => 'A red bag with a distinctive strap.',
        ])->assertCreated();
        $report = ItemReport::sole();
        $this->assertSame('Actual Student Finder', $report->finder_name);
        $this->assertSame('001-2026-90', $report->finder_student_id);
        $student->profile->update(['full_name' => 'Updated Profile Name', 'school_id_number' => 'CHANGED-ID']);
        $this->actingAs($this->admin())->get(route('reports.show', $report))->assertOk()
            ->assertSee('id="finderNameValue">Actual Student Finder', false)
            ->assertSee('id="finderStudentIdValue">001-2026-90', false);
    }

    public function test_recorded_finder_name_is_shown_even_if_legacy_surrender_flag_is_false(): void
    {
        $report = ItemReport::factory()->found()->create([
            'reporter_user_id' => $this->admin()->id, 'surrendered_by_student' => false,
            'finder_name' => 'Recorded Finder', 'finder_student_id' => 'RECORDED-001',
        ]);
        $this->actingAs($this->admin())->get(route('reports.show', $report))->assertOk()
            ->assertSee('id="finderNameValue">Recorded Finder', false)->assertSee('RECORDED-001');
    }

    public function test_unknown_finder_on_admin_report_is_not_replaced_by_the_admin_identity(): void
    {
        $reporter = $this->admin();
        UserProfile::create(['user_id' => $reporter->id, 'full_name' => 'Submitting Admin', 'school_id_number' => 'ADMIN-PRIVATE-ID', 'user_type' => 'admin']);
        $report = ItemReport::factory()->found()->create(['reporter_user_id' => $reporter->id]);
        $this->actingAs($reporter)->get(route('reports.show', $report))->assertOk()
            ->assertSee('id="finderNameValue">Not recorded', false)->assertSee('Submitted By')->assertSee('Submitting Admin')
            ->assertDontSee('ADMIN-PRIVATE-ID');
    }

    public function test_legacy_contact_override_name_does_not_inherit_another_persons_student_id(): void
    {
        $student = $this->student();
        $report = ItemReport::factory()->found()->create(['reporter_user_id' => $student->id, 'reporter_name' => 'Separately Named Finder']);
        $this->actingAs($this->admin())->get(route('reports.show', $report))->assertOk()
            ->assertSee('id="finderNameValue">Separately Named Finder', false)->assertDontSee('001-2026-90');
    }

    public function test_student_override_is_snapshotted_without_copying_the_submitters_id(): void
    {
        Mail::fake();
        $this->actingAs($this->student())->postJson(route('reports.store'), [
            'submission_id' => (string) Str::uuid(), 'report_type' => 'found', 'reporter_name' => 'Actual Student Finder', 'item_description' => 'A blue bag found near the office.',
            'contact_override' => '09171234567', 'reporter_name' => 'Another Finder',
        ])->assertCreated();
        $this->assertSame('Another Finder', ItemReport::sole()->finder_name);
        $this->assertNull(ItemReport::sole()->finder_student_id);
    }

    public function test_regular_users_see_reporter_name_without_private_student_id(): void
    {
        $student = $this->student();
        $report = ItemReport::factory()->found()->create(['reporter_user_id' => $student->id, 'finder_name' => 'Private Finder', 'finder_student_id' => 'PRIVATE-ID']);
        $this->actingAs($student)->get(route('reports.show', $report))->assertOk()->assertDontSee('Finder\'s Name')->assertSee('Reporter Name')->assertSee('Private Finder')->assertDontSee('PRIVATE-ID');
    }

    public function test_dashboard_restores_original_claimed_label_and_keeps_successful_claim_metric(): void
    {
        $this->actingAs($this->admin())->get(route('dashboard'))->assertOk()
            ->assertSee('>Claimed</span>', false)->assertDontSee('Awaiting review')->assertDontSee('Awaiting Review')->assertSee('Claimed Items');
    }
}
