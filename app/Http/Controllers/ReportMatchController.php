<?php

namespace App\Http\Controllers;

use App\Models\ItemReport;
use App\Models\ReportMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportMatchController extends WebBaseController
{
    public function index(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $status = (string) $request->query('status', 'suggested');
        if (!in_array($status, ['suggested','confirmed','rejected'], true)) {
            $status = 'suggested';
        }

        $matches = ReportMatch::query()
            ->where('status', $status)
            ->orderByDesc('score')
            ->paginate(20)
            ->withQueryString();

        return view('matches.index', compact('matches','status'));
    }

    public function confirm(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $match = ReportMatch::findOrFail($id);

        DB::transaction(function () use ($request, $match) {
            $lost = ItemReport::findOrFail((int) $match->lost_report_id);
            $found = ItemReport::findOrFail((int) $match->found_report_id);

            $oldLost = $lost->status;
            $oldFound = $found->status;

            $match->update(['status' => 'confirmed']);

            $lost->update([
                'status' => 'matched',
                'matched_report_id' => $found->id,
                'matched_score' => $match->score,
            ]);

            $found->update([
                'status' => 'matched',
                'matched_report_id' => $lost->id,
                'matched_score' => $match->score,
            ]);

            $this->pushReportStatus($lost->id, $oldLost, 'matched', (int) $this->user()->id, 'Match confirmed');
            $this->pushReportStatus($found->id, $oldFound, 'matched', (int) $this->user()->id, 'Match confirmed');

            $this->notify((int) $lost->reporter_user_id, 'match', 'Match confirmed', 'A match for your lost report was confirmed.', [
                'lost_report_id' => $lost->id,
                'found_report_id' => $found->id,
                'score' => (float) $match->score,
            ]);

            $this->notify((int) $found->reporter_user_id, 'match', 'Match confirmed', 'A match for your found report was confirmed.', [
                'lost_report_id' => $lost->id,
                'found_report_id' => $found->id,
                'score' => (float) $match->score,
            ]);

            $this->audit($request, 'matches.confirm', 'report_matches', $match->id, ['lost' => $lost->id, 'found' => $found->id]);
        });

        return redirect()->route('matches.index', ['status' => 'suggested'])->with('success', 'Confirmed');
    }

    public function reject(Request $request, int $id)
    {
        $this->requireAnyRole(['admin','osa']);

        $match = ReportMatch::findOrFail($id);

        $data = $request->validate([
            'note' => ['nullable','string','max:255'],
        ]);

        $match->update(['status' => 'rejected']);
        $this->audit($request, 'matches.reject', 'report_matches', $match->id, ['note' => $data['note'] ?? null]);

        return redirect()->route('matches.index', ['status' => 'suggested'])->with('success', 'Rejected');
    }

    public function createManual(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $data = $request->validate([
            'lost_report_id' => ['required','integer','exists:item_reports,id'],
            'found_report_id' => ['required','integer','exists:item_reports,id'],
            'score' => ['required','numeric','between:0,100'],
            'method' => ['required', Rule::in(['keyword','nlp','manual','ai_image_recognition'])],
        ]);

        $lost = ItemReport::findOrFail((int) $data['lost_report_id']);
        $found = ItemReport::findOrFail((int) $data['found_report_id']);

        if ($lost->report_type !== 'lost' || $found->report_type !== 'found') {
            return back()->withErrors(['message' => 'Invalid report types for pairing']);
        }

        $row = ReportMatch::where('lost_report_id', $lost->id)->where('found_report_id', $found->id)->first();
        if ($row) {
            $row->update(['score' => $data['score'], 'method' => $data['method'], 'status' => $row->status]);
        } else {
            $row = ReportMatch::create([
                'lost_report_id' => $lost->id,
                'found_report_id' => $found->id,
                'score' => $data['score'],
                'method' => $data['method'],
                'status' => 'suggested',
            ]);
        }

        $this->audit($request, 'matches.manual_create', 'report_matches', $row->id, $data);

        return redirect()->route('matches.index', ['status' => 'suggested'])->with('success', 'Saved');
    }
}
