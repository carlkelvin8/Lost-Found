<?php

namespace App\Console\Commands;

use App\Services\UploadedImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClearReportDataCommand extends Command
{
    protected $signature = 'reports:clear-data {--execute : Delete the listed report data} {--force : Allow execution in production}';

    protected $description = 'Preview or clear report activity and item photos while retaining accounts, profiles, and reference data';

    public function handle(UploadedImageStorage $images): int
    {
        $tables = ['claim_documents', 'report_matches', 'claims', 'report_status_history', 'report_photos', 'item_reports', 'notifications'];
        $this->info('Environment: ' . app()->environment());
        $this->table(['Data', 'Records'], array_map(fn ($table) => [$table, DB::table($table)->count()], $tables));
        $this->info('Retaining ' . DB::table('users')->count() . ' accounts and ' . DB::table('user_profiles')->count() . ' profiles, including profile pictures.');
        $disk = Storage::disk($images->diskName());
        $photoKeys = collect($disk->allFiles('report_photos'))->merge(
            DB::table('report_photos')->pluck('photo_url')->map(fn ($path) => $images->key($path))
        )->filter(fn ($key) => $key && str_starts_with($key, 'report_photos/'))->unique()->values();
        $this->info('Item photo files: ' . $photoKeys->count());
        if (!$this->option('execute')) {
            $this->info('Preview only. No data was deleted.');
            return self::SUCCESS;
        }
        if (app()->isProduction() && !$this->option('force')) {
            $this->error('Production execution requires --force.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($tables) {
            foreach ($tables as $table) DB::table($table)->delete();
            DB::table('activity_logs')->whereIn('entity_type', ['item_reports', 'report_matches', 'claims', 'report_photos'])->delete();
        });
        foreach ($photoKeys as $key) {
            $images->delete($key);
            if ($disk->exists($key)) {
                $this->error('Could not remove item photo: ' . $key);
                return self::FAILURE;
            }
        }
        foreach (['admin_pending_reports', 'admin_suggested_matches', 'admin_total_reports', 'admin_report_status', 'admin_report_type', 'admin_match_status'] as $key) Cache::forget($key);
        DB::table('users')->select('id')->orderBy('id')->each(function ($user) {
            Cache::forget("user_{$user->id}_reports_count");
            Cache::forget("user_{$user->id}_claims_count");
        });
        $this->info('Report data cleared. Accounts, profiles, profile pictures, categories, locations, and departments retained.');
        return self::SUCCESS;
    }
}
