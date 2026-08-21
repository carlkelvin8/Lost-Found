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

        $isStaff = $this->hasAnyRole(['admin', 'osa']);

        $stats = [
            'my_reports' => Cache::remember("user_{$user->id}_reports_count", 300, fn() =>
                ItemReport::where('reporter_user_id', $user->id)->count()
            ),
            'my_claims' => Cache::remember("user_{$user->id}_claims_count", 300, fn() =>
                Claim::where('claimant_user_id', $user->id)->count()
            ),
            'pending_reports' => $isStaff
                ? Cache::remember('staff_pending_reports', 120, fn() =>
                    ItemReport::where('status', 'pending')->count()
                )
                : 0,
            'suggested_matches' => $isStaff
                ? Cache::remember('staff_suggested_matches', 120, fn() =>
                    ReportMatch::where('status', 'suggested')->count()
                )
                : 0,
        ];

        if ($isStaff) {
            $reportStatus = Cache::remember('staff_report_status', 300, fn() =>
                ItemReport::select('status', DB::raw('count(*) as c'))
                    ->groupBy('status')
                    ->pluck('c', 'status')
                    ->all()
            );

            $reportType = Cache::remember('staff_report_type', 300, fn() =>
                ItemReport::select('report_type', DB::raw('count(*) as c'))
                    ->groupBy('report_type')
                    ->pluck('c', 'report_type')
                    ->all()
            );

            $matchStatus = Cache::remember('staff_match_status', 300, fn() =>
                ReportMatch::select('status', DB::raw('count(*) as c'))
                    ->groupBy('status')
                    ->pluck('c', 'status')
                    ->all()
            );

            $stats = array_merge($stats, [
                'total_users' => Cache::remember('staff_total_users', 600, fn() => User::count()),
                'total_reports' => Cache::remember('staff_total_reports', 300, fn() => ItemReport::count()),
                'report_status' => $reportStatus,
                'report_type' => $reportType,
                'match_status' => $matchStatus,
            ]);
        }

        $recentReports = ItemReport::where('reporter_user_id', $user->id)
            ->with(['category', 'location'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentActivity = $isStaff
            ? ItemReport::with(['category', 'location', 'reporter'])
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get()
            : collect();

        return view('dashboard', compact(
            'user',
            'stats',
            'isStaff',
            'recentReports',
            'recentActivity'
        ));
    }
}
