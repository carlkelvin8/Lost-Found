<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ItemReport;
use App\Models\ReportMatch;
use App\Models\User;
use Illuminate\Http\Request;
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
            // User-side
            'my_reports' => ItemReport::where('reporter_user_id', $user->id)->count(),
            'my_claims'  => Claim::where('claimant_user_id', $user->id)->count(),

            // Staff-side
            'pending_reports' => $isStaff
                ? ItemReport::where('status', 'pending')->count()
                : 0,

            'suggested_matches' => $isStaff
                ? ReportMatch::where('status', 'suggested')->count()
                : 0,
        ];

        if ($isStaff) {
            $reportStatus = ItemReport::select('status', DB::raw('count(*) as c'))
                ->groupBy('status')
                ->pluck('c', 'status')
                ->all();

            $reportType = ItemReport::select('report_type', DB::raw('count(*) as c'))
                ->groupBy('report_type')
                ->pluck('c', 'report_type')
                ->all();

            $matchStatus = ReportMatch::select('status', DB::raw('count(*) as c'))
                ->groupBy('status')
                ->pluck('c', 'status')
                ->all();

            $stats = array_merge($stats, [
                'total_users' => User::count(),
                'total_reports' => ItemReport::count(),
                'report_status' => $reportStatus,
                'report_type' => $reportType,
                'match_status' => $matchStatus,
            ]);
        }

        // Recent reports for user
        $recentReports = ItemReport::where('reporter_user_id', $user->id)
            ->with(['category', 'location'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent activity for staff
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
