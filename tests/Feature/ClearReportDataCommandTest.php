<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClearReportDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_keeps_data_and_execution_clears_only_report_activity_and_photos(): void
    {
        Storage::fake('public');
        config(['filesystems.uploads' => 'public']);
        $user = User::factory()->create();
        $user->profile()->create(['full_name' => 'Preserved User', 'user_type' => 'guest', 'avatar_url' => 'avatars/keep.png']);
        $report = ItemReport::factory()->found()->create(['reporter_user_id' => $user->id]);
        Claim::factory()->create(['report_id' => $report->id]);
        $report->photos()->create(['photo_url' => 'report_photos/remove.png', 'created_at' => now()]);
        DB::table('notifications')->insert(['user_id' => $user->id, 'title' => 'Report activity', 'notif_type' => 'report']);
        Storage::disk('public')->put('avatars/keep.png', 'avatar');
        Storage::disk('public')->put('report_photos/remove.png', 'photo');
        Storage::disk('public')->put('report_photos/orphan.png', 'orphan');
        Cache::put("user_{$user->id}_reports_count", 1);
        $users = DB::table('users')->count();
        $this->artisan('reports:clear-data')->assertSuccessful();
        $this->assertDatabaseCount('item_reports', 1);
        Storage::disk('public')->assertExists('report_photos/remove.png');
        $this->artisan('reports:clear-data', ['--execute' => true])->assertSuccessful();
        foreach (['item_reports', 'claims', 'report_photos', 'notifications'] as $table) $this->assertDatabaseCount($table, 0);
        $this->assertDatabaseCount('users', $users);
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'avatar_url' => 'avatars/keep.png']);
        Storage::disk('public')->assertExists('avatars/keep.png');
        Storage::disk('public')->assertMissing(['report_photos/remove.png', 'report_photos/orphan.png']);
        $this->assertNull(Cache::get("user_{$user->id}_reports_count"));
    }
}
