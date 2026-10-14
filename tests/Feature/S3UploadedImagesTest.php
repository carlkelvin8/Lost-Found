<?php

namespace Tests\Feature;

use App\Models\ItemReport;
use App\Models\ReportPhoto;
use App\Models\User;
use App\Services\UploadedImageStorage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class S3UploadedImagesTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.default' => 's3', 'filesystems.uploads' => null]);
        Storage::fake('s3');
        Storage::disk('s3')->buildTemporaryUrlsUsing(fn ($path, $expiry) => 'https://bucket.example/'.$path.'?expires='.$expiry->timestamp);
        Mail::fake();
        Queue::fake();
    }

    public function test_report_creation_uploads_to_bucket_and_renders_signed_urls(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('reports.store'), [
            'submission_id' => (string) Str::uuid(), 'report_type' => 'found',
            'reporter_name' => 'Test Finder', 'item_description' => 'A distinctive blue wallet near the library.',
            'photos' => [UploadedFile::fake()->image('wallet.png', 200, 200)],
        ])->assertCreated();
        $photo = ReportPhoto::sole();
        Storage::disk('s3')->assertExists($photo->photo_url);
        $this->assertFalse(str_starts_with($photo->photo_url, 'https://'));
        $this->get(route('reports.show', $photo->report_id))->assertOk()->assertSee('https://bucket.example/'.$photo->photo_url.'?expires=');
        $images = app(UploadedImageStorage::class);
        $this->assertNotEmpty($images->read($photo->photo_url));
        $this->assertSame($images->read($photo->photo_url), $images->read('storage/'.$photo->photo_url));
        $firstUrl = $images->url($photo->photo_url);
        $this->travel(2)->hours();
        $this->assertNotSame($firstUrl, $images->url($photo->photo_url));
    }

    public function test_avatar_replacement_uploads_to_bucket_and_removes_old_object(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['full_name' => 'Image Tester', 'user_type' => 'guest', 'avatar_url' => 'avatars/old.png']);
        Storage::disk('s3')->put('avatars/old.png', 'old-image');
        $this->actingAs($user)->post(route('profile.update'), [
            'full_name' => 'Image Tester', 'avatar' => UploadedFile::fake()->image('avatar.png'),
        ])->assertRedirect();
        $avatar = $user->profile->fresh()->avatar_url;
        Storage::disk('s3')->assertExists($avatar);
        Storage::disk('s3')->assertMissing('avatars/old.png');
        $this->get(route('profile.edit'))->assertOk()->assertSee('https://bucket.example/'.$avatar.'?expires=');
    }

    public function test_separate_photo_upload_and_delete_use_the_bucket(): void
    {
        $user = User::factory()->create();
        $report = ItemReport::factory()->lost()->create(['reporter_user_id' => $user->id, 'locked_at' => null]);
        $this->actingAs($user)->post(route('reports.photos.store', $report), ['photo' => UploadedFile::fake()->image('item.png')])->assertRedirect();
        $photo = ReportPhoto::sole();
        Storage::disk('s3')->assertExists($photo->photo_url);
        $this->post(route('photos.destroy', $photo))->assertRedirect();
        Storage::disk('s3')->assertMissing($photo->photo_url);
        $this->assertDatabaseCount('report_photos', 0);
    }

    public function test_upload_disk_can_be_a_custom_cloud_disk_and_external_urls_remain_usable(): void
    {
        config(['filesystems.uploads' => 'cloud_bucket', 'filesystems.disks.cloud_bucket' => config('filesystems.disks.s3')]);
        Storage::fake('cloud_bucket');
        Storage::disk('cloud_bucket')->buildTemporaryUrlsUsing(fn ($path) => 'https://cloud.example/'.$path.'?signed=1');
        $images = app(UploadedImageStorage::class);
        $key = $images->store(UploadedFile::fake()->image('avatar.png'), 'avatars');
        Storage::disk('cloud_bucket')->assertExists($key);
        $this->assertSame('https://cloud.example/'.$key.'?signed=1', $images->url($key));
        $this->assertSame('https://example.com/avatar.png', $images->url('https://example.com/avatar.png'));
    }

    public function test_real_s3_adapter_generates_signed_urls_without_accessing_the_bucket(): void
    {
        config(['filesystems.disks.s3' => [
            'driver' => 's3', 'key' => 'test-key', 'secret' => 'test-secret', 'region' => 'auto',
            'bucket' => 'test-bucket', 'endpoint' => 'https://s3.example', 'use_path_style_endpoint' => true,
        ]]);
        Storage::forgetDisk('s3');
        $images = app(UploadedImageStorage::class);
        $url = $images->url('storage/report_photos/item.png');
        $this->assertStringStartsWith('https://s3.example/test-bucket/report_photos/item.png?', $url);
        $this->assertStringContainsString('X-Amz-Signature=', $url);
        $this->assertStringContainsString('X-Amz-Expires=3600', $url);
        $this->assertSame('report_photos/item.png', $images->key($url));
    }

    public function test_ai_and_pdf_exports_read_private_bucket_images(): void
    {
        $file = UploadedFile::fake()->image('item.png', 200, 200);
        $contents = file_get_contents($file->getPathname());
        Storage::disk('s3')->put('report_photos/private.png', $contents);
        $method = new \ReflectionMethod(\App\Services\OpenAIService::class, 'getImageAsBase64');
        $this->assertSame(base64_encode($contents), $method->invoke(app(\App\Services\OpenAIService::class), 'report_photos/private.png'));
        $user = User::factory()->create();
        $user->roles()->firstOrCreate(['name' => 'admin']);
        $report = ItemReport::factory()->found()->create();
        $report->photos()->create(['photo_url' => 'report_photos/private.png', 'created_at' => now()]);
        $response = $this->actingAs($user)->get(route('reports.exportSinglePdf', $report))->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
