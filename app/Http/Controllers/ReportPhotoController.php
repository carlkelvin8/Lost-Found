<?php

namespace App\Http\Controllers;

use App\Models\ItemReport;
use App\Models\ReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportPhotoController extends WebBaseController
{
    public function store(Request $request, int $reportId)
    {
        if (!$this->user()) return redirect()->route('login');

        $report = ItemReport::findOrFail($reportId);

        $isStaff = $this->hasAnyRole(['admin','osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if (in_array($report->status, ['returned','archived'], true)) {
            return redirect()->route('reports.show', $reportId)->withErrors(['message' => 'Report is locked']);
        }

        $data = $request->validate([
            'photo' => ['required','file','mimes:jpg,jpeg,png,webp','max:4096'],
            'caption' => ['nullable','string','max:255'],
        ]);

        $path = $data['photo']->store('report_photos', 'public');
        // Store relative path so asset() helper works with any domain/port
        $url = 'storage/' . $path;

        ReportPhoto::create([
            'report_id' => $report->id,
            'photo_url' => $url,
            'caption' => $data['caption'] ?? null,
            'created_at' => now(),
        ]);

        $this->audit($request, 'photos.create', 'report_photos', null, ['report_id' => $report->id]);

        return redirect()->route('reports.show', $reportId)->with('success', 'Uploaded');
    }

    public function destroy(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $photo = ReportPhoto::findOrFail($id);
        $report = ItemReport::findOrFail((int) $photo->report_id);

        $isStaff = $this->hasAnyRole(['admin','osa']);
        $isOwner = (int) $report->reporter_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if (in_array($report->status, ['returned','archived'], true)) {
            return redirect()->route('reports.show', $report->id)->withErrors(['message' => 'Report is locked']);
        }

        // Delete file from storage if it exists
        if ($photo->photo_url) {
            $path = str_replace('storage/', '', $photo->photo_url);
            Storage::disk('public')->delete($path);
        }

        $photo->delete();
        $this->audit($request, 'photos.delete', 'report_photos', $id, ['report_id' => $report->id]);

        return redirect()->route('reports.edit', $report->id)->with('success', 'Deleted');
    }
}
