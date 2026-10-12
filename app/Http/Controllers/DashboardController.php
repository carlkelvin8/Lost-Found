<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\ReportMatch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends WebBaseController
{
    public function index(Request $request)
    {
        $user = $this->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $stats = [
            'my_reports' => Cache::remember("user_{$user->id}_reports_count", 300, fn() =>
                ItemReport::where('reporter_user_id', $user->id)->count()
            ),
            'my_claims' => Cache::remember("user_{$user->id}_claims_count", 300, fn() =>
                Claim::where('claimant_user_id', $user->id)->count()
            ),
        ];

        // Analytics Overview is shown to Admin-role accounts only. OSA and all
        // other user types keep the exact same dashboard layout as everyone else.
        $showAnalytics = $this->hasAnyRole(['admin']);

        if ($showAnalytics) {
            $stats['pending_reports'] = Cache::remember('admin_pending_reports', 120, fn() =>
                ItemReport::where('status', 'pending')->count()
            );
            $stats['suggested_matches'] = Cache::remember('admin_suggested_matches', 120, fn() =>
                ReportMatch::where('status', 'suggested')->count()
            );
            $stats['total_users'] = Cache::remember('admin_total_users', 600, fn() => User::count());
            $stats['total_reports'] = Cache::remember('admin_total_reports', 300, fn() => ItemReport::count());
            $stats['report_status'] = Cache::remember('admin_report_status', 300, fn() =>
                ItemReport::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status')->all()
            );
            $stats['report_type'] = Cache::remember('admin_report_type', 300, fn() =>
                ItemReport::select('report_type', DB::raw('count(*) as c'))->groupBy('report_type')->pluck('c', 'report_type')->all()
            );
            $stats['match_status'] = Cache::remember('admin_match_status', 300, fn() =>
                ReportMatch::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status')->all()
            );
        }

        $recentReports = ItemReport::where('reporter_user_id', $user->id)
            ->with(['category', 'location'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('user', 'stats', 'showAnalytics', 'recentReports'));
    }
}
