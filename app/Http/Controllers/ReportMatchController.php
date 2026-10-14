<?php

namespace App\Http\Controllers;

use App\Models\ItemReport;
use App\Models\ReportMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportMatchController extends WebBaseController
{
    public function search(Request $request)
    {
        $this->requireAnyRole(['admin', 'osa']);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:190'],
            'type' => ['nullable', Rule::in(['lost', 'found'])],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
        ]);
        $query = ItemReport::with(['category', 'location'])->whereIn('status', ['pending', 'matched']);
        foreach (['category_id', 'location_id'] as $field) {
            if (!empty($filters[$field])) $query->where($field, $filters[$field]);
        }
        if (!empty($filters['type'])) $query->where('report_type', $filters['type']);
        $term = trim($filters['q'] ?? '');
        if ($term !== '') {
            // Literal substring search; SQL wildcards in user text have no special meaning.
            $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
            $query->where(function ($q) use ($term) {
                foreach (['item_name', 'item_description', 'brand_model', 'color', 'circumstances'] as $field) {
                    $q->orWhereRaw("$field LIKE ? ESCAPE '!'", ['%' . $term . '%']);
                }
            });
        }
        $reports = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $categories = \App\Models\Category::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();
        return view('matches.search', compact('reports', 'categories', 'locations', 'filters'));
    }

    public function index(Request $request)
    {
        $this->requireAnyRole(['admin','osa']);

        $status = (string) $request->query('status', 'suggested');
        if (!in_array($status, ['suggested','confirmed','rejected'], true)) {
            $status = 'suggested';
        }

        $matches = ReportMatch::query()
            ->with(['lostReport', 'foundReport'])
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

        DB::transaction(function () use ($request, $match) {
            $match->update(['status' => 'rejected']);

            // Re-evaluate report statuses if they were matched due to this match
            $lost = ItemReport::find((int) $match->lost_report_id);
            $found = ItemReport::find((int) $match->found_report_id);

            if ($lost && $lost->status === 'matched') {
                $hasOtherMatches = ReportMatch::where('lost_report_id', $lost->id)
                    ->whereIn('status', ['confirmed', 'suggested'])
                    ->exists();
                if (!$hasOtherMatches) {
                    $oldStatus = $lost->status;
                    $lost->update(['status' => 'pending', 'matched_report_id' => null, 'matched_score' => null]);
                    $this->pushReportStatus($lost->id, $oldStatus, 'pending', (int) $this->user()->id, 'Match rejected');
                }
            }

            if ($found && $found->status === 'matched') {
                $hasOtherMatches = ReportMatch::where('found_report_id', $found->id)
                    ->whereIn('status', ['confirmed', 'suggested'])
                    ->exists();
                if (!$hasOtherMatches) {
                    $oldStatus = $found->status;
                    $found->update(['status' => 'pending', 'matched_report_id' => null, 'matched_score' => null]);
                    $this->pushReportStatus($found->id, $oldStatus, 'pending', (int) $this->user()->id, 'Match rejected');
                }
            }

            $this->audit($request, 'matches.reject', 'report_matches', $match->id);
        });

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

        $row = DB::transaction(function () use ($lost, $found, $data) {
            $reports = ItemReport::whereIn('id', [$lost->id, $found->id])->orderBy('id')->lockForUpdate()->get();
            if ($reports->contains(fn ($report) => !in_array($report->status, ['pending', 'matched'], true))) {
                throw \Illuminate\Validation\ValidationException::withMessages(['message' => 'Only active reports can be paired.']);
            }
            $existing = ReportMatch::where('lost_report_id', $lost->id)->where('found_report_id', $found->id)->first();
            if ($existing) {
                throw \Illuminate\Validation\ValidationException::withMessages(['message' => 'This pair already exists. Its score and review status have been preserved.']);
            }
            return ReportMatch::create([
                'lost_report_id' => $lost->id,
                'found_report_id' => $found->id,
                'score' => $data['score'],
                'method' => $data['method'],
                'status' => 'suggested',
            ]);
        });

        $this->audit($request, 'matches.manual_create', 'report_matches', $row->id, $data);

        return redirect()->route('matches.index', ['status' => 'suggested'])->with('success', 'Saved');
    }
}
