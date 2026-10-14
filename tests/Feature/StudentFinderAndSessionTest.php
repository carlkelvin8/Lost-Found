<?php

namespace Tests\Feature;

use App\Models\ItemReport;
use App\Models\ReportMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentFinderAndSessionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->roles()->firstOrCreate(['name' => 'admin']);
        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'submission_id' => (string) Str::uuid(),
            'report_type' => 'found',
            'item_description' => 'A surrendered wallet with a distinctive blue strap.',
            'surrendered_by_student' => true,
            'finder_name' => '  Juan Dela Cruz  ',
            'finder_student_id' => '  001-2026-123  ',
        ], $overrides);
    }

    public function test_admin_records_student_finder_without_contact_override_or_registered_finder(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $this->actingAs($admin)->postJson(route('reports.store'), $this->payload())->assertCreated();
        $report = ItemReport::sole();
        $this->assertTrue($report->surrendered_by_student);
        $this->assertSame('Juan Dela Cruz', $report->finder_name);
        $this->assertSame('001-2026-123', $report->finder_student_id);
        $this->assertEquals($admin->id, $report->reporter_user_id);
        $this->get(route('reports.show', $report))->assertOk()->assertSee('Juan Dela Cruz')->assertSee('001-2026-123');
    }

    public function test_student_surrender_rejects_missing_whitespace_and_invalid_details(): void
    {
        $this->actingAs($this->admin());
        $this->postJson(route('reports.store'), $this->payload(['finder_name' => '  ', 'finder_student_id' => '  ']))
            ->assertUnprocessable()->assertJsonValidationErrors(['finder_name', 'finder_student_id']);
        $this->postJson(route('reports.store'), $this->payload(['finder_name' => "\u{00A0}\u{2003}", 'finder_student_id' => "\u{00A0}\u{2003}"]))
            ->assertUnprocessable()->assertJsonValidationErrors(['finder_name', 'finder_student_id']);
        $data = $this->payload();
        unset($data['finder_name'], $data['finder_student_id']);
        $this->postJson(route('reports.store'), $data)->assertUnprocessable()->assertJsonValidationErrors(['finder_name', 'finder_student_id']);
        $this->postJson(route('reports.store'), $this->payload(['finder_student_id' => '<script>']))->assertUnprocessable()->assertJsonValidationErrors('finder_student_id');
        $this->assertDatabaseCount('item_reports', 0);
    }

    public function test_regular_users_and_lost_reports_cannot_forge_student_surrenders(): void
    {
        $this->actingAs(User::factory()->create())->postJson(route('reports.store'), $this->payload())
            ->assertUnprocessable()->assertJsonValidationErrors(['surrendered_by_student', 'finder_name', 'finder_student_id']);
        $this->actingAs($this->admin())->postJson(route('reports.store'), $this->payload(['report_type' => 'lost']))
            ->assertUnprocessable()->assertJsonValidationErrors('surrendered_by_student');
        $this->assertDatabaseCount('item_reports', 0);
    }

    public function test_admins_own_found_and_lost_reports_do_not_require_student_details(): void
    {
        Mail::fake();
        $this->actingAs($this->admin());
        foreach (['found', 'lost'] as $type) {
            $data = $this->payload(['report_type' => $type, 'surrendered_by_student' => false]);
            unset($data['finder_name'], $data['finder_student_id']);
            $this->postJson(route('reports.store'), $data)->assertCreated();
        }
        $this->assertDatabaseCount('item_reports', 2);
    }

    public function test_matched_claimant_cannot_see_finder_name_or_student_id(): void
    {
        $claimant = User::factory()->create();
        $lost = ItemReport::factory()->lost()->create(['reporter_user_id' => $claimant->id]);
        $found = ItemReport::factory()->found()->create(['surrendered_by_student' => true, 'finder_name' => 'Private Finder', 'finder_student_id' => 'PRIVATE-001']);
        ReportMatch::factory()->create(['lost_report_id' => $lost->id, 'found_report_id' => $found->id]);
        $this->actingAs($claimant)->get(route('reports.show', $found))->assertOk()->assertDontSee('Private Finder')->assertDontSee('PRIVATE-001');
    }

    public function test_legacy_admin_edit_preserves_real_type_and_validates_finder_details(): void
    {
        Mail::fake();
        $report = ItemReport::factory()->found()->create(['status' => 'pending', 'locked_at' => null]);
        $data = $this->payload(['report_type' => 'lost', 'status' => 'pending', 'finder_name' => '  ', 'finder_student_id' => '  ']);
        $this->actingAs($this->admin())->postJson(route('reports.update', $report), $data)->assertUnprocessable()->assertJsonValidationErrors(['finder_name', 'finder_student_id']);
        $data['finder_name'] = 'Updated Finder';
        $data['finder_student_id'] = '000-123';
        $this->postJson(route('reports.update', $report), $data)->assertRedirect();
        $this->assertSame('found', $report->fresh()->report_type);
        $this->assertSame('000-123', $report->fresh()->finder_student_id);
    }

    public function test_retry_preserves_one_student_surrender_report(): void
    {
        Mail::fake();
        $data = $this->payload();
        $first = $this->actingAs($this->admin())->postJson(route('reports.store'), $data)->assertCreated();
        $this->postJson(route('reports.store'), $data)->assertOk()->assertJson(['redirect' => $first->json('redirect')]);
        $this->assertDatabaseCount('item_reports', 1);
    }

    public function test_new_account_entry_creates_a_distinct_context_each_time(): void
    {
        $first = $this->get(route('auth.new-session'))->assertRedirect()->headers->get('Location');
        $second = $this->get(route('auth.new-session'))->assertRedirect()->headers->get('Location');
        $this->assertNotSame($first, $second);
        $this->assertMatchesRegularExpression('#/session/[a-f0-9-]{36}/login$#', $first);
    }

    public function test_scoped_login_cookie_is_httponly_and_context_alone_cannot_authenticate(): void
    {
        $context = (string) Str::uuid();
        $name = config('session.cookie') . '_tab_' . str_replace('-', '', $context);
        $response = $this->get('/session/' . $context . '/login')->assertOk();
        $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === $name);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('/session/' . $context, $cookie->getPath());
        $this->get('/session/' . $context . '/dashboard')->assertRedirect('/session/' . $context . '/login');
        $this->get('/session/not-a-uuid/login')->assertNotFound();
    }

    public function test_new_finder_migration_preserves_legacy_reports_on_rollback(): void
    {
        $report = ItemReport::factory()->create();
        $migration = require database_path('migrations/2026_10_14_000002_add_student_finder_details.php');
        $migration->down();
        $this->assertDatabaseHas('item_reports', ['id' => $report->id]);
        $migration->up();
        $this->assertDatabaseHas('item_reports', ['id' => $report->id, 'surrendered_by_student' => false, 'finder_student_id' => null]);
    }

    public function test_scoped_report_id_reaches_controller_without_context_becoming_the_id(): void
    {
        $context = (string) Str::uuid();
        $guard = 'isolated_' . str_replace('-', '', $context);
        config(['auth.guards.' . $guard => ['driver' => 'session', 'provider' => 'users']]);
        $user = User::factory()->create();
        $report = ItemReport::factory()->create(['reporter_user_id' => $user->id]);
        $this->actingAs($user, $guard)->get('/session/' . $context . '/reports/' . $report->id)
            ->assertOk()->assertViewHas('report', fn ($row) => $row->id === $report->id);
    }
}
