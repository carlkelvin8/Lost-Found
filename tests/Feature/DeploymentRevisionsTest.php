<?php

namespace Tests\Feature;

use App\Jobs\DeliverNotificationEmail;
use App\Mail\NotificationMail;
use App\Models\ItemReport;
use App\Models\Location;
use App\Models\Notification;
use App\Models\ReportMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeploymentRevisionsTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\ReportPayload;

    private function payload(array $overrides = []): array
    {
        return array_replace($this->completeReportFields(), [
            'submission_id' => (string) Str::uuid(), 'report_type' => 'lost',
            'item_description' => 'A blue wallet with a distinctive silver strap.',
        ], $overrides);
    }

    public function test_missing_whitespace_forged_and_wrongly_typed_details_are_rejected_without_writing(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['item_name', 'category_id', 'color', 'item_description'] as $field) {
            $data = $this->payload([$field => '   ']);
            $this->postJson(route('reports.store'), $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->postJson(route('reports.store'), $this->payload(['location_detail' => "\u{00a0}\u{2003}"]))
            ->assertUnprocessable()->assertJsonValidationErrors('location_id');
        $this->postJson(route('reports.store'), $this->payload(['category_id' => 999999]))->assertUnprocessable()->assertJsonValidationErrors('category_id');
        $this->postJson(route('reports.store'), $this->payload(['location_id' => 999999]))->assertUnprocessable()->assertJsonValidationErrors('location_id');
        foreach (['item_name', 'color', 'item_description', 'location_detail', 'brand_model'] as $field) {
            $this->postJson(route('reports.store'), $this->payload([$field => ['invalid']]))->assertUnprocessable();
        }
        $this->postJson(route('reports.store'), $this->payload(['report_type' => 'found', 'reporter_name' => ['invalid']]))->assertUnprocessable()->assertJsonValidationErrors('reporter_name');
        $this->assertDatabaseCount('item_reports', 0);
    }

    public function test_custom_room_is_normalized_displayed_and_does_not_create_master_records(): void
    {
        $user = User::factory()->create();
        $location = Location::create(['name' => 'Building A']);
        $data = $this->payload(['location_id' => $location->id, 'location_detail' => '  Room   301  ', 'brand_model' => '', 'contact_override' => '']);
        $this->actingAs($user)->postJson(route('reports.store'), $data)->assertCreated();
        $report = ItemReport::sole();
        $this->assertSame('Room 301', $report->location_detail);
        $this->assertSame('Building A · Room 301', $report->location_label);
        $this->get(route('reports.show', $report))->assertOk()->assertSee('Building A · Room 301');
        $data['submission_id'] = (string) Str::uuid();
        $this->postJson(route('reports.store'), $data)->assertCreated();
        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_edit_uses_the_same_required_fields_and_preserves_existing_relationships(): void
    {
        $user = User::factory()->create();
        $report = ItemReport::factory()->lost()->create(['reporter_user_id' => $user->id, 'locked_at' => null]);
        $this->actingAs($user)->postJson(route('reports.update', $report), $this->payload(['item_name' => ' ']))
            ->assertUnprocessable()->assertJsonValidationErrors('item_name');
        $this->postJson(route('reports.update', $report), $this->payload(['location_detail' => 'Building B Room 205']))->assertRedirect();
        $this->assertSame('Building B Room 205', $report->fresh()->location_detail);
    }

    public function test_each_eligible_item_has_exactly_one_claim_link_and_an_identifiable_card(): void
    {
        $user = User::factory()->create();
        $lost = ItemReport::factory()->lost()->create(['reporter_user_id' => $user->id]);
        $found = ItemReport::factory()->found()->count(2)->create(['status' => 'matched']);
        foreach ($found as $item) {
            ReportMatch::factory()->create(['lost_report_id' => $lost->id, 'found_report_id' => $item->id]);
        }
        $html = $this->actingAs($user)->get(route('reports.show', $lost))->assertOk()->getContent();
        foreach ($found as $item) {
            $this->assertSame(1, substr_count($html, 'href="'.route('claims.create', $item->id).'"'));
            $this->assertStringContainsString('Report #'.$item->id, $html);
        }
        $html = $this->get(route('reports.show', $found[0]))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'href="'.route('claims.create', $found[0]->id).'"'));
        $this->post(route('claims.store'), ['report_id' => $found[0]->id, 'proof_text' => str_repeat(' ', 30)])->assertSessionHasErrors('proof_text');
        $this->assertDatabaseCount('claims', 0);
    }

    public function test_photo_limits_are_consistent_and_corrupted_or_spoofed_images_are_rejected(): void
    {
        Storage::fake('public');
        Queue::fake();
        $this->actingAs(User::factory()->create());
        $bad = UploadedFile::fake()->createWithContent('fake.jpg', '<script>invalid image</script>');
        $this->postJson(route('reports.store'), $this->payload(['photos' => [$bad]]))->assertUnprocessable()->assertJsonValidationErrors('photos.0');
        $small = UploadedFile::fake()->image('small.png', 20, 20);
        $this->postJson(route('reports.store'), $this->payload(['photos' => [$small]]))->assertUnprocessable()->assertJsonValidationErrors('photos.0');
        $large = UploadedFile::fake()->image('large.jpg', 200, 200)->size(11000);
        $this->postJson(route('reports.store'), $this->payload(['photos' => [$large]]))->assertUnprocessable()->assertJsonValidationErrors('photos.0');
        $mobile = UploadedFile::fake()->image('mobile.jpg', 4100, 1000);
        $this->postJson(route('reports.store'), $this->payload(['photos' => [$mobile]]))->assertCreated();
        $six = array_map(fn ($i) => UploadedFile::fake()->image("photo{$i}.png", 200, 200), range(1, 6));
        $this->postJson(route('reports.store'), $this->payload(['photos' => $six]))->assertUnprocessable()->assertJsonValidationErrors('photos');
    }

    public function test_single_photo_route_cannot_exceed_five_or_bypass_image_validation(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $report = ItemReport::factory()->lost()->create(['reporter_user_id' => $user->id, 'locked_at' => null]);
        $this->actingAs($user)->postJson(route('reports.photos.store', $report), ['photo' => UploadedFile::fake()->create('fake.heic', 20)])
            ->assertUnprocessable()->assertJsonValidationErrors('photo');
        for ($i = 0; $i < 5; $i++) {
            $report->photos()->create(['photo_url' => "report_photos/{$i}.jpg", 'created_at' => now()]);
        }
        $this->postJson(route('reports.photos.store', $report), ['photo' => UploadedFile::fake()->image('sixth.jpg', 200, 200)])
            ->assertUnprocessable()->assertJsonValidationErrors('photo');
        $this->assertSame(5, $report->photos()->count());
    }

    public function test_notification_email_is_durable_and_only_sent_by_worker(): void
    {
        Mail::fake();
        $staff = User::factory()->create();
        $staff->roles()->firstOrCreate(['name' => 'admin']);
        $this->actingAs(User::factory()->create())->postJson(route('reports.store'), $this->payload())->assertCreated();
        $notification = Notification::where('user_id', $staff->id)->sole();
        $this->assertDatabaseCount('jobs', 1);
        Mail::assertNothingSent();
        (new DeliverNotificationEmail($notification->id))->handle();
        Mail::assertSent(NotificationMail::class, fn ($mail) => $mail->hasTo($staff->email));
    }

    public function test_location_migration_rollback_retains_existing_reports_and_relationships(): void
    {
        $location = Location::create(['name' => 'Library']);
        $report = ItemReport::factory()->create(['location_id' => $location->id, 'location_detail' => 'Reading room']);
        $migration = require database_path('migrations/2026_11_09_000001_add_report_location_detail.php');
        $migration->down();
        $this->assertDatabaseHas('item_reports', ['id' => $report->id, 'location_id' => $location->id]);
        $migration->up();
        $this->assertDatabaseHas('item_reports', ['id' => $report->id, 'location_id' => $location->id, 'location_detail' => null]);
    }
}
