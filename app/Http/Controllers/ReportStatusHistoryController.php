<?php

namespace App\Http\Controllers;

use App\Models\ReportStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportStatusHistoryController extends WebBaseController
{
    public function index(Request $request, int $reportId)
    {
        if (!$this->user()) {
            return redirect()->route('login');
        }

        $isStaff = $this->hasAnyRole(['admin', 'osa']);

        // If not staff, owner lang pwede
        if (!$isStaff) {
            $own = DB::table('item_reports')
                ->where('id', $reportId)
                ->where('reporter_user_id', (int) $this->user()->id)
                ->exists();

            if (!$own) {
                abort(403);
            }
        }

        $history = ReportStatusHistory::where('report_id', $reportId)
            ->orderByDesc('changed_at')
            ->paginate(20);

        return view('reports.history', [
            'history'  => $history,
            'reportId' => $reportId,
            'isStaff'  => $isStaff,
        ]);
    }
}
