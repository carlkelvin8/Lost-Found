<?php

namespace Tests\Feature;

use App\Models\ItemReport;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadedImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_disk_images_render_and_serve_in_web_admin_and_isolated_sessions(): void
    {
        Storage::fake('public');
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
        Storage::disk('public')->put('avatars/legacy.png', $image);
        Storage::disk('public')->put('report_photos/item.png', $image);
        $user = User::factory()->create();
        $user->roles()->firstOrCreate(['name' => 'admin']);
        UserProfile::create(['user_id' => $user->id, 'full_name' => 'Image Tester', 'user_type' => 'admin', 'avatar_url' => 'storage/avatars/legacy.png']);
        $report = ItemReport::factory()->found()->create(['reporter_user_id' => $user->id]);
        $report->photos()->create(['photo_url' => 'storage/report_photos/item.png', 'created_at' => now()]);

        $context = '12345678-1234-4234-8234-123456789012';
        $scopedGuard = 'isolated_' . str_replace('-', '', $context);
        config(['auth.guards.' . $scopedGuard => ['driver' => 'session', 'provider' => 'users']]);
        foreach (['' => 'web', '/admin' => 'admin', '/session/' . $context => $scopedGuard] as $prefix => $guard) {
            $this->actingAs($user, $guard)->get($prefix . '/reports/' . $report->id)->assertOk()
                ->assertSee($prefix . '/storage/avatars/legacy.png')
                ->assertSee($prefix . '/storage/report_photos/item.png');
            $response = $this->get($prefix . '/storage/report_photos/item.png')->assertOk();
            $this->assertSame($image, file_get_contents($response->baseResponse->getFile()->getPathname()));
        }
    }

    public function test_urls_support_direct_public_uploads_and_legacy_storage_paths(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/example.png', 'image');
        $this->assertSame(url('/storage/avatars/example.png'), uploaded_image_url('avatars/example.png'));
        $this->assertSame(url('/storage/avatars/example.png'), uploaded_image_url('storage/avatars/example.png'));
        $this->assertSame(asset('images/naap-logo.png'), uploaded_image_url('images/naap-logo.png'));
        $this->assertSame('https://example.com/image.png', uploaded_image_url('https://example.com/image.png'));
        $this->assertSame('', uploaded_image_url('../private/image.png'));
    }

    public function test_missing_images_return_not_found_and_guests_cannot_access_storage(): void
    {
        Storage::fake('public');
        $this->get('/storage/avatars/missing.png')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/storage/avatars/missing.png')->assertNotFound();
    }
}
