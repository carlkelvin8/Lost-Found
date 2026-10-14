<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\ReportMatch;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientReportedIssuesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->roles()->firstOrCreate(['name' => 'admin']);
        return $user;
    }

    private function reportData(string $type = 'found'): array
    {
        return ['submission_id' => (string) Str::uuid(), 'report_type' => $type, 'item_description' => 'A distinctive wallet with a blue strap.'];
    }

    public function test_rejection_reason_is_persistent_visible_and_cannot_be_replaced(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $claim = Claim::factory()->create();
        $this->actingAs($admin)->post(route('claims.reject', $claim), ['rejection_reason' => '  Ownership proof does not match.  '])->assertRedirect();
        $this->assertSame('Ownership proof does not match.', $claim->fresh()->rejection_reason);
        $this->assertEquals($admin->id, $claim->fresh()->reviewed_by_user_id);
        $this->assertNotNull($claim->fresh()->reviewed_at);
        $this->get(route('claims.show', $claim))->assertOk()->assertSee('Ownership proof does not match.');
        $this->post(route('claims.reject', $claim), ['rejection_reason' => 'Replacement'])->assertSessionHasErrors();
        $this->assertSame('Ownership proof does not match.', $claim->fresh()->rejection_reason);
        $this->actingAs($claim->claimant)->get(route('claims.show', $claim))->assertOk()->assertSee('Ownership proof does not match.');
        $this->actingAs($claim->report->reporter)->get(route('reports.history', $claim->report_id))->assertOk()->assertDontSee('Ownership proof does not match.');
        $this->actingAs(User::factory()->create())->get(route('claims.show', $claim))->assertForbidden();
    }

    public function test_empty_rejection_and_unauthorized_rejection_are_rejected(): void
    {
        $claim = Claim::factory()->create();
        $this->actingAs($this->admin())->postJson(route('claims.reject', $claim), ['rejection_reason' => '   '])->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');
        $this->assertSame('pending', $claim->fresh()->status);
        $this->actingAs($claim->claimant)->postJson(route('claims.reject', $claim), ['rejection_reason' => 'Reason'])->assertForbidden();
    }

    public function test_lost_and_found_retries_create_one_record_and_separate_ids_allow_similar_reports(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        foreach (['lost', 'found'] as $type) {
            $payload = $this->reportData($type);
            $first = $this->actingAs($user)->postJson(route('reports.store'), $payload)->assertCreated();
            $retry = $this->postJson(route('reports.store'), $payload)->assertOk();
            $this->assertSame($first->json('redirect'), $retry->json('redirect'));
            $this->assertDatabaseCount('item_reports', $type === 'lost' ? 1 : 3);
            $payload['submission_id'] = (string) Str::uuid();
            $this->postJson(route('reports.store'), $payload)->assertCreated();
        }
        $this->assertDatabaseCount('item_reports', 4);
        $this->assertDatabaseCount('activity_logs', 4);
    }

    public function test_submission_identifier_is_required_and_scoped_to_account(): void
    {
        Mail::fake();
        $payload = $this->reportData();
        $this->actingAs(User::factory()->create())->postJson(route('reports.store'), $payload)->assertCreated();
        $this->actingAs(User::factory()->create())->postJson(route('reports.store'), $payload)->assertCreated();
        unset($payload['submission_id']);
        $this->postJson(route('reports.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('submission_id');
        $this->assertDatabaseCount('item_reports', 2);
    }

    public function test_retry_does_not_duplicate_automatic_matches_or_notifications(): void
    {
        Mail::fake();
        ItemReport::factory()->found()->create(['item_name' => 'Wallet', 'color' => 'Blue', 'brand_model' => 'Brand', 'incident_date' => now()->toDateString()]);
        $payload = $this->reportData('lost') + ['item_name' => 'Wallet', 'color' => 'Blue', 'brand_model' => 'Brand', 'incident_date' => now()->toDateString()];
        $this->actingAs(User::factory()->create())->postJson(route('reports.store'), $payload)->assertCreated();
        $this->assertDatabaseCount('report_matches', 1);
        $this->assertDatabaseCount('notifications', 2);
        $this->postJson(route('reports.store'), $payload)->assertOk();
        $this->assertDatabaseCount('report_matches', 1);
        $this->assertDatabaseCount('notifications', 2);
        Mail::assertSent(\App\Mail\NotificationMail::class, 2);
    }

    public function test_database_unique_constraint_rejects_duplicate_submission_even_without_controller(): void
    {
        $report = ItemReport::factory()->create(['submission_id' => (string) Str::uuid()]);
        $this->expectException(QueryException::class);
        ItemReport::factory()->create(['reporter_user_id' => $report->reporter_user_id, 'submission_id' => $report->submission_id]);
    }

    public function test_found_override_requires_name_but_lost_and_found_without_override_do_not(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());
        $data = $this->reportData();
        $data['contact_override'] = '09171234567';
        $data['reporter_name'] = '   ';
        $this->postJson(route('reports.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('reporter_name');
        $data['reporter_name'] = '  Juan Dela Cruz  ';
        $this->postJson(route('reports.store'), $data)->assertCreated();
        $this->assertSame('Juan Dela Cruz', ItemReport::sole()->reporter_name);
        $lost = $this->reportData('lost') + ['contact_override' => '09171234567'];
        $this->postJson(route('reports.store'), $lost)->assertCreated();
        $this->postJson(route('reports.store'), $this->reportData())->assertCreated();
    }

    public function test_validation_returns_json_without_creating_or_uploading_photos(): void
    {
        Queue::fake();
        $photo = UploadedFile::fake()->image('camera.jpg', 200, 200);
        $payload = $this->reportData();
        $payload['photos'] = [$photo];
        $payload['item_description'] = 'short';
        $this->actingAs(User::factory()->create())->postJson(route('reports.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('item_description');
        $this->assertDatabaseCount('item_reports', 0);
        $this->assertDatabaseCount('report_photos', 0);
        $payload['item_description'] = 'The same captured image can now be submitted.';
        $first = $this->postJson(route('reports.store'), $payload)->assertCreated();
        $this->postJson(route('reports.store'), $payload)->assertOk()->assertJson(['redirect' => $first->json('redirect')]);
        $this->assertDatabaseCount('report_photos', 1);
        Queue::assertPushed(\App\Jobs\ProcessImageAnalysis::class, 1);
        $path = public_path(\App\Models\ReportPhoto::sole()->photo_url);
        if (is_file($path)) unlink($path); // Only the fixture upload created by this test.
    }

    public function test_manual_search_filters_active_reports_and_does_not_mutate_matches(): void
    {
        $active = ItemReport::factory()->found()->create(['item_name' => 'Blue wallet']);
        ItemReport::factory()->found()->create(['item_name' => 'Blue wallet', 'status' => 'returned']);
        ItemReport::factory()->lost()->create(['item_name' => 'Blue wallet']);
        $this->actingAs($this->admin())->get(route('matches.search', ['q' => 'Blue wallet', 'type' => 'found']))->assertOk()->assertViewHas('reports', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $active->id);
        $this->get(route('matches.search', ['q' => 'no such item']))->assertOk()->assertSee('No active reports match your search.');
        $this->assertDatabaseCount('report_matches', 0);
        $this->actingAs(User::factory()->create())->get(route('matches.search'))->assertForbidden();
    }

    public function test_manual_pair_preserves_existing_automatic_match(): void
    {
        $match = ReportMatch::factory()->create(['score' => 88, 'method' => 'keyword']);
        $this->actingAs($this->admin())->post(route('matches.manual'), ['lost_report_id' => $match->lost_report_id, 'found_report_id' => $match->found_report_id, 'score' => 1, 'method' => 'manual'])->assertSessionHasErrors();
        $this->assertEquals(88, $match->fresh()->score);
        $this->assertSame('keyword', $match->fresh()->method);
    }

    public function test_claimed_items_counts_unique_approved_reports_and_refreshes_after_approval(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $report = ItemReport::factory()->found()->create(['status' => 'returned']);
        Claim::factory()->count(2)->create(['report_id' => $report->id, 'status' => 'approved']);
        Claim::factory()->create(['status' => 'rejected']);
        Claim::factory()->create(['status' => 'cancelled']);
        $pending = Claim::factory()->create();
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertViewHas('stats', fn ($stats) => $stats['claimed_items'] === 1);
        $this->post(route('claims.approve', $pending))->assertRedirect();
        $this->get(route('dashboard'))->assertOk()->assertViewHas('stats', fn ($stats) => $stats['claimed_items'] === 2);
        $this->get('/dashboard/stats')->assertOk()->assertJsonPath('claimed_count', 2);
    }

    public function test_landing_demo_uses_cellphone(): void
    {
        $this->get('/')->assertOk()->assertSee('<strong>Cellphone</strong>', false)->assertDontSee('iPhone 14 Pro');
    }

    public function test_admin_and_web_guards_keep_distinct_identities_and_logout(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $this->actingAs($admin, 'admin')->actingAs($user, 'web');
        $response = $this->get('/admin/dashboard')->assertOk()->assertViewHas('user', fn ($identity) => $identity->id === $admin->id);
        $response->assertSee('/admin/logout')->assertSee('/admin/reports/create');
        $this->get('/dashboard')->assertOk()->assertViewHas('user', fn ($identity) => $identity->id === $user->id);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertNull(Auth::guard('web')->user());
        $this->assertEquals($admin->id, Auth::guard('admin')->id());
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_regular_account_cannot_use_admin_boundary(): void
    {
        $this->actingAs(User::factory()->create(), 'admin')->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_login_issues_a_distinct_cookie_path(): void
    {
        $name = config('session.cookie');
        $response = $this->get('/admin/login')->assertOk()->assertSee('/admin/login');
        $cookies = $response->headers->getCookies();
        $sessionCookie = collect($cookies)->first(fn ($cookie) => $cookie->getName() === $name . '_admin');
        $this->assertNotNull($sessionCookie);
        $this->assertSame('/admin', $sessionCookie->getPath());
        $this->assertTrue($sessionCookie->isHttpOnly());
    }

    public function test_manual_search_applies_category_location_and_literal_wildcards(): void
    {
        $category = \App\Models\Category::create(['name' => 'Electronics']);
        $location = \App\Models\Location::create(['name' => 'Library']);
        $report = ItemReport::factory()->found()->create(['category_id' => $category->id, 'location_id' => $location->id, 'item_name' => 'Phone 100%']);
        ItemReport::factory()->found()->create(['item_name' => 'Phone 1000']);
        $this->actingAs($this->admin())->get(route('matches.search', ['q' => '100%', 'category_id' => $category->id, 'location_id' => $location->id]))
            ->assertOk()->assertViewHas('reports', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $report->id);
    }

    public function test_manual_pair_creates_an_active_pair_and_rejects_terminal_reports(): void
    {
        $lost = ItemReport::factory()->lost()->create();
        $found = ItemReport::factory()->found()->create();
        $payload = ['lost_report_id' => $lost->id, 'found_report_id' => $found->id, 'score' => 82, 'method' => 'manual'];
        $this->actingAs($this->admin())->post(route('matches.manual'), $payload)->assertRedirect();
        $this->assertDatabaseHas('report_matches', $payload + ['status' => 'suggested']);
        $found = ItemReport::factory()->found()->create(['status' => 'returned']);
        $payload['found_report_id'] = $found->id;
        $this->post(route('matches.manual'), $payload)->assertSessionHasErrors();
        $this->assertDatabaseCount('report_matches', 1);
    }

    public function test_legacy_pending_claim_cannot_reopen_a_finalized_item(): void
    {
        Mail::fake();
        $claim = Claim::factory()->create();
        $claim->report->update(['status' => 'returned']);
        $this->actingAs($this->admin())->post(route('claims.approve', $claim))->assertSessionHasErrors();
        $this->post(route('claims.reject', $claim), ['rejection_reason' => 'Already returned to its owner.'])->assertRedirect();
        $this->assertSame('returned', $claim->report->fresh()->status);
    }

    public function test_migration_rolls_back_new_fields_without_removing_existing_records(): void
    {
        $report = ItemReport::factory()->create();
        $migration = require database_path('migrations/2026_10_14_000001_add_audit_workflow_fields.php');
        $migration->down();
        $this->assertDatabaseHas('item_reports', ['id' => $report->id]);
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('item_reports', 'submission_id'));
        $migration->up();
        $this->assertDatabaseHas('item_reports', ['id' => $report->id, 'submission_id' => null]);
    }
}
