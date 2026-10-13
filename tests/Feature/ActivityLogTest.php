<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_action_is_recorded_and_displayed_with_its_actor(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->firstOrCreate(['name' => 'admin']);

        $this->actingAs($admin)
            ->withServerVariables(['REMOTE_ADDR' => '192.0.2.10', 'HTTP_USER_AGENT' => 'Audit regression test'])
            ->post(route('categories.store'), ['name' => 'Electronics'])
            ->assertRedirect(route('categories.index'));

        $log = ActivityLog::sole();
        $this->assertSame('categories.create', $log->action);
        $this->assertEquals($admin->id, $log->user_id);
        $this->assertEquals(Category::sole()->id, $log->entity_id);
        $this->assertSame('categories', $log->entity_type);
        $this->assertSame('192.0.2.10', $log->ip_address);
        $this->assertSame('Audit regression test', $log->user_agent);
        $this->assertSame('Electronics', $log->properties['name']);
        $this->assertTrue($log->causer->is($admin));
        $this->assertNotNull($log->created_at);

        $this->get(route('activity_logs.index'))
            ->assertOk()
            ->assertSee($admin->email)
            ->assertSee('categories.create');
    }

    public function test_osa_can_filter_logs_by_action_entity_and_user(): void
    {
        $osa = User::factory()->create();
        $osa->roles()->firstOrCreate(['name' => 'osa']);
        ActivityLog::create([
            'user_id' => $osa->id,
            'action' => 'categories.create',
            'entity_type' => 'categories',
            'entity_id' => 42,
        ]);
        ActivityLog::create(['user_id' => $osa->id, 'action' => 'categories.delete']);
        ActivityLog::create(['action' => 'auth.login']);

        $this->actingAs($osa)->get(route('activity_logs.index', [
            'action' => 'categories.create',
            'entity_type' => 'categories',
            'entity_id' => 42,
            'user_id' => $osa->id,
        ]))
            ->assertOk()
            ->assertSee($osa->email)
            ->assertSee('categories.create')
            ->assertDontSee('categories.delete')
            ->assertDontSee('auth.login')
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
    }

    public function test_properties_are_saved_in_the_existing_metadata_column(): void
    {
        $log = ActivityLog::create([
            'action' => 'reports.update',
            'properties' => ['status' => 'returned'],
        ])->fresh();

        $this->assertSame(['status' => 'returned'], $log->properties);
        $this->assertSame(['status' => 'returned'], json_decode($log->meta_json, true));
    }

    public function test_logs_remain_readable_after_the_actor_is_deleted(): void
    {
        $actor = User::factory()->create();
        $log = ActivityLog::create(['user_id' => $actor->id, 'action' => 'auth.login']);
        $actor->delete();
        $this->assertNull($log->fresh()->causer);

        $admin = User::factory()->create();
        $admin->roles()->firstOrCreate(['name' => 'admin']);
        $this->actingAs($admin)->get(route('activity_logs.index'))
            ->assertOk()->assertSee('auth.login');
    }

    public function test_regular_users_cannot_read_audit_logs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('activity_logs.index'))->assertForbidden();
    }
}
