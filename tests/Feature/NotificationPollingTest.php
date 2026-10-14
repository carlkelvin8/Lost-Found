<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationPollingTest extends TestCase
{
    use RefreshDatabase;

    public function test_polling_counts_new_unread_records_instead_of_the_record_id(): void
    {
        $user = User::factory()->create();
        $old = Notification::create(['user_id' => $user->id, 'notif_type' => 'match', 'title' => 'Existing match']);
        $this->actingAs($user)->getJson(route('notifications.check'))->assertJson(['count' => 1, 'latest' => $old->id, 'new_count' => 0]);
        $new = Notification::create(['user_id' => $user->id, 'notif_type' => 'claim', 'title' => 'New claim']);
        Notification::create(['user_id' => User::factory()->create()->id, 'notif_type' => 'claim', 'title' => 'Another account']);
        $this->getJson(route('notifications.check', ['after_id' => $old->id]))->assertJson(['count' => 2, 'latest' => $new->id, 'new_count' => 1]);
        $this->getJson(route('notifications.check', ['after_id' => $new->id]))->assertJsonPath('new_count', 0);
        Notification::where('user_id', $user->id)->update(['read_at' => now()]);
        $this->getJson(route('notifications.check', ['after_id' => 0]))->assertJson(['count' => 0, 'new_count' => 0]);
    }

    public function test_new_reports_notify_staff_once_and_login_has_no_extra_account_tab_button(): void
    {
        Mail::fake();
        $admin = User::factory()->create();
        $admin->roles()->firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $data = ['submission_id' => (string) Str::uuid(), 'report_type' => 'lost', 'item_description' => 'A blue wallet lost in the library.'];
        $this->actingAs($user)->postJson(route('reports.store'), $data)->assertCreated();
        $this->postJson(route('reports.store'), $data)->assertOk();
        $this->assertSame(1, Notification::where('user_id', $admin->id)->where('notif_type', 'report')->count());
        $this->assertSame(0, Notification::where('user_id', $user->id)->count());
        auth()->logout();
        $this->get(route('login'))->assertOk()->assertDontSee('Open another account in a separate tab');
        $this->get(route('admin.login'))->assertOk()->assertDontSee('Open another account in a separate tab');
    }
}
